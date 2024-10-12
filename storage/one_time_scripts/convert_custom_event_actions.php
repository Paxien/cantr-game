<?php

/**
 * Script that converts old, button-based custom actions to property-based custom actions.
 */
error_reporting(E_ALL);
$page = "convert_custom_event_actions";
include("../../lib/stddef.inc.php");

$db = Db::get();

$stm = $db->prepare("SELECT * FROM objecttypes WHERE
     show_instructions_inventory LIKE '%area_event%'
  OR show_instructions_inventory LIKE '%custom_event%'
  OR show_instructions_outside LIKE '%area_event%'
  OR show_instructions_outside LIKE '%custom_event%' ");
$stm->execute();

function renameTag($fromName, $toName, Db $db)
{
  $stm = $db->prepare("UPDATE texts SET name = :toName WHERE name = :fromName");
  $stm->bindStr("toName", $toName);
  $stm->bindStr("fromName", $fromName);
  $stm->execute();
  echo "TEXT $fromName -> $toName\n";
}

function getExistingPropertyDetails($objectTypeId, Db $db)
{
  $stm = $db->prepare("SELECT details FROM obj_properties WHERE objecttype_id = :typeId");
  $stm->bindInt("typeId", $objectTypeId);
  $existingResult = $stm->executeScalar();
  if (empty($existingResult)) {
    return [];
  }
  return json_decode($existingResult, true);
}

function updatePropertyDetails($objectTypeId, $detailsJson, Db $db)
{
  $stm = $db->prepare("REPLACE INTO obj_properties (objecttype_id, property_type, details)
    VALUES (:typeId, 'CustomEvent', :details)");
  $stm->bindInt("typeId", $objectTypeId);
  $stm->bindStr("details", $detailsJson);
  $stm->execute();

  echo "PROPERTY TO " . $detailsJson . "\n";
}


function transformButtons($objectType, $instructions, $columnName, Db $db)
{
  if (array_key_exists("buttons", $instructions)) {
    $buttons = $instructions["buttons"];
    echo "OLD BUTTONS: " . $buttons . "\n";
    $buttons = explode(",", $buttons);
    $newButtons = [];
    foreach ($buttons as $button) {
      list($image, $action, $altText) = explode(">", $button);
      if (StringUtil::startsWith($action, "custom_event")) {
        list($actionName, $eventActor, $eventObserver) = explode("/", $action);

        $newActorEvent = "custom_" . str_replace("custom_", "", str_replace("_owner", "_actor", $eventActor)); // single "custom_" prefix and "_actor" suffix
        renameTag($eventActor, $newActorEvent, $db);

        $newOthersEvent = "custom_" . str_replace("_observer", "_others", $eventObserver);
        renameTag($eventObserver, $newOthersEvent, $db);

        $eventBase = str_replace("_owner", "", $eventActor);
        $newButtons[] = $image . ">custom_event/" . $eventBase . ">" . $altText;

        $newPropertyDetails = [
          "actorEventTag" => $newActorEvent,
          "othersEventTag" => $newOthersEvent,
        ];

        $currentDetails = getExistingPropertyDetails($objectType->id, $db);

        $currentDetails[$eventBase] = $newPropertyDetails;
        updatePropertyDetails($objectType->id, json_encode($currentDetails), $db);
      } elseif (StringUtil::startsWith($action, "area_event")) {
        list($actionName, $range, $eventBase) = explode("/", $action);

        $newButtons[] = $image . ">custom_event/" . $eventBase . ">" . $altText;

        $newPropertyDetails = [
          "actorEventTag" => "custom_" . $eventBase . "_actor",
          "othersEventTag" => "custom_" . $eventBase . "_others",
          "distantEventTag" => "custom_" . $eventBase . "_distant",
          "distantRange" => $range,
          "onlyOutside" => true,
        ];

        $currentDetails = getExistingPropertyDetails($objectType->id, $db);
        $currentDetails[$eventBase] = $newPropertyDetails;
        updatePropertyDetails($objectType->id, json_encode($currentDetails), $db);
      } else {
        $newButtons[] = $button;
      }
    }

    $newButtonsString = "buttons:" . implode(",", $newButtons);
    $stm = $db->prepare("UPDATE objecttypes SET $columnName = :newData WHERE id = :id");
    $stm->bindInt("id", $objectType->id);
    $stm->bindStr("newData", $newButtonsString);
    echo "NEW BUTTONS: " . $newButtonsString . "\n";
    $stm->execute();
  }
}

$abc = [];
foreach ($stm->fetchAll() as $objectType) {
  $instructions = Parser::rulesToArray($objectType->show_instructions_inventory);
  echo "\nfor $objectType->name\n";
  transformButtons($objectType, $instructions, "show_instructions_inventory", $db);

  $instructions = Parser::rulesToArray($objectType->show_instructions_outside);
  transformButtons($objectType, $instructions, "show_instructions_outside", $db);

  $abc[] = $objectType->name;
}

echo "CHANGED OBJECTTYPES: " . json_encode($abc);

// buttons:basketball>custom_event/throwball_owner/throwball_observer>alt_throw_ball,football>custom_event/kickball_owner/kickball_observer>alt_kick_ball
// buttons:firework>area_event/45/red_flare>alt_ignite_firework
