<?php

class ObjectsList
{
  const TYPE_LOCATION = 1;
  const TYPE_INVENTORY = 2;
  const SHOW_ALL = 5;

  public static function generateObjectsArray($location, Character $char, $l, $sub = self::SHOW_ALL)
  {
    $objectIds = self::getOrderedObjectIds($location, $sub);

    $requestString = "inventory";
    if ($location instanceof Location) {
      $requestString = "object";
    }

    $objects = CObject::bulkLoadByIds($objectIds);
    
    $longTag = [];
    $shortTag = [];
    if (count($objects) > 0) {
      foreach ($objects as $object) {
        $longTag["item_" . $object->getUniqueName() . "_o"]  = true;
        $shortTag["item_" . $object->getUniqueName() . "_t"] = true;
      }
      
      $names = [];
      // object name cache
      $db = Db::Get();
      $longTagStm = $db->prepareWithList("SELECT name, content, grammar FROM texts
        WHERE name IN (:names) AND language = :language", [
          "names" => array_keys($longTag),
      ]);
      $longTagStm->bindInt("language", $l);
      $longTagStm->execute();
      foreach ($longTagStm->fetchAll() as $tLong) {
        $names[$tLong->name]['transfer_long'] = $tLong->content;
        $names[$tLong->name]['grammar']= $tLong->grammar;
        unset($longTag[$tLong->name]);
      }
      $shortTagStm = $db->prepareWithList("SELECT name, content, grammar FROM texts
        WHERE name IN (:names) AND language = :language", [
        "names" => array_keys($shortTag),
      ]);
      $shortTagStm->bindInt("language", $l);
      $shortTagStm->execute();
      foreach ($shortTagStm->fetchAll() as $tShort) {
        $names[$tShort->name]['transfer'] = $tShort->content;
        unset($shortTag[$tShort->name]);
      }
      
      if ($l != 1) {
        if (count($longTag) > 0) {
          $longTagStm = $db->prepareWithList("SELECT name, content, grammar FROM texts
            WHERE name IN (:names) AND language = :language", [
            "names" => array_keys($longTag),
          ]);
          $longTagStm->bindInt("language", LanguageConstants::ENGLISH);
          $longTagStm->execute();
          foreach ($longTagStm->fetchAll() as $tLong) {
            $names[$tLong->name]['transfer_long'] = $tLong->content;
            $names[$tLong->name]['grammar']= $tLong->grammar;
          }
        }
        if (count($shortTag) > 0) {
          $shortTagStm = $db->prepareWithList("SELECT name, content, grammar FROM texts
        WHERE name IN (:names) AND language = :language", [
            "names" => array_keys($shortTag),
          ]);
          $shortTagStm->bindInt("language", LanguageConstants::ENGLISH);
          $shortTagStm->execute();
          foreach ($shortTagStm->fetchAll() as $tShort) {
            $names[$tShort->name]['transfer'] = $tShort->content;
          }
        }
      }
    }

    $descriptionsArray = Descriptions::getDescriptionsArray($objectIds, Descriptions::TYPE_OBJECT);
    
    $objectsArray = [];
    foreach ($objects as $object) {

      $object->visibleDescription = $descriptionsArray[$object->getId()]; // supply object with its description (for performance)
      $object->wasDescChecked = true;
      $uniqueName = $object->getUniqueName();
      $object->names = array(
        "unique_name" => $uniqueName, // not a mistake, objecttype->unique_name can be changed later in func show()
        "transfer_long" => $names["item_{$uniqueName}_o"]["transfer_long"],
        "grammar" => $names["item_{$uniqueName}_o"]["grammar"],
        "transfer" => $names["item_{$uniqueName}_t"]["transfer"]
      );
      $objectView = new ObjectView($object, Character::loadById($char->getId()));
      $datagot = $objectView->show($requestString);
      
      $obinfo = array();
      $obinfo['id'] = $object->getId();
      $obinfo['amount'] = $object->getAmount();
      $obinfo['isQuantity'] = $object->isQuantity() ? 1 : 0;
      $obinfo['unitWeight'] = $object->getUnitWeight();
      $obinfo['name'] = $datagot->text;
      $obinfo['buttons'] = $datagot->buttons;
      
      $objectsArray[] = $obinfo;
    }
    return $objectsArray;
  }

  public static function getOrderedObjectIds($location, $sub = self::SHOW_ALL)
  {
    if ($location instanceof Location) {
      $whereQuery = "location = " . $location->getId();
      $sortQuery = "row_order DESC, setting, type, id DESC";
    } elseif ($location instanceof Character) {
      $categories = array (
        1 => "AND (type IN (1, 37) OR
            type IN (SELECT op.objecttype_id FROM obj_properties op WHERE op.property_type = 'NoteStorage'))", // notes and envelopes
        2 => "AND (type NOT IN (1, 30, 1270, 37, 413, 414, 415, 416, 417, 418, 576, 577, 578, 579, 580, 808 ) AND
            type NOT IN (SELECT op.objecttype_id FROM obj_properties op WHERE op.property_type = 'NoteStorage'))", // raws'n'items
        3 => "AND type IN ( 413, 414, 415,416, 417, 418, 576, 577, 578, 579, 580, 808 ) ", // coins
        4 => "AND type IN (30, 1270)", // keys and keyrings
        5 => ""); // all
      $whereQuery = "person = " . $location->getId() . " " . $categories[$sub] . " AND (specifics IS NULL OR specifics NOT LIKE '%wearing:1%')";
      $sortQuery = "type, specifics, typeid DESC, id DESC";
    } elseif ($location instanceof CObject) {
      $whereQuery = "attached = " . $location->getId() . " AND setting != " . ObjectConstants::SETTING_FIXED;
      $sortQuery = "ordering, row_order DESC, setting, type, id";
    } else {
      throw new InvalidArgumentException("trying to list place which isn't a location, container nor inventory");
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT id,
    (IF (type = :noticeboard, 10," /* noticeboard */ ."
      IF(type IN (12,13,14,138), 9," /* lock*/ ."
          IF (type = 483, 8," /* window */ ."
            IF (type IN (25,26,36,317), 7, " /* engines */ ."
              IF (type = 1, 6, " /* notes */ ."
                IF (type = 2, 5, 0) " /* raws */ ."
              )
            )
          )
        )
      )
    ) AS row_order
    FROM objects WHERE $whereQuery
    ORDER BY $sortQuery");
    $stm->bindInt("noticeboard", ObjectConstants::TYPE_NOTICEBOARD);
    $stm->execute();
    $objectIds = $stm->fetchScalars();
    return $objectIds;
  }
}
