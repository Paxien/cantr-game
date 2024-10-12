<?php

$objectId = HTTPContext::getInteger("object_id");

if ($objectId > 0) {
  try {
    $object = CObject::loadById($objectId);
  } catch (InvalidArgumentException $e) {
    CError::throwRedirectTag("", "error_too_far_away");
  }
  
  $isInInventory = ($char->getId() == $object->getPerson());
  $isInSameLoc = ($char->getLocation() > 0) && ($char->getLocation() == $object->getLocation());
  
  if (!($isInInventory || $isInSameLoc)) {
    CError::throwRedirectTag("", "error_too_far_away");
  }
}

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("", "error_too_far_away");
}

function newRow(Project $project, $amountNeeded = 0) {
  global $char;
  $tag = new Tag($project->getName(), false);
  $tag->content = $tag->interpret();
  $projectName = $tag->interpret();

  $initiatorName = "";
  try {
    $initiator = Character::loadById($project->getInitiator());
    if ($char->isInSameLocationAs($initiator)) {
      $initiatorTag = TagBuilder::forChar($initiator)->observedBy($char)->allowHtml(false)->build();
      $initiatorTag->content = $initiatorTag->interpret();
      $initiatorName = $initiatorTag->interpret();
    }
  } catch (InvalidArgumentException $e) {}

  $projectData = array("id" => $project->getId(), "name" => $projectName, "initiator" => $initiatorName);
  if ($amountNeeded > 0) {
    $projectData["maxPossible"] = intval($amountNeeded);
  }
  return $projectData;
}


$projectsData = array();
function get_projects($projectsData, $projects, CObject $object) {
  foreach ($projects as $projectId) {
    try {
      $project = Project::loadById($projectId);
      if ($object === null) {
        $projectsData[] = newRow($project);
      } else {
        $left = 0;
        $reqLeft = Parser::rulesToArray($project->getReqLeft());
        if ($object->getType() == ObjectConstants::TYPE_RAW) {
          if (isset($reqLeft['raws'])) {
            $rawsLeft = Parser::rulesToArray($reqLeft['raws'], ",>");
            $ownedRawName = ObjectHandler::getRawNameFromId($object->getTypeid());
            $left = $rawsLeft[$ownedRawName];
          }
        } else {
          if (isset($reqLeft['objects'])) {
            $objectsLeft = Parser::rulesToArray($reqLeft['objects'], ",>");
            $left = $objectsLeft[$object->getName()];
          }
        }
        if ($left > 0) {
          $projectsData[] = newRow($project, $left);
        }
      }
    } catch (InvalidArgumentException $e) {
    }
  }
  return $projectsData;
}

$db = Db::get();
$stm = $db->prepare("SELECT id, initiator = :charId AS ownproject FROM projects WHERE location = :locationId ORDER BY ownproject DESC, id DESC");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
$projects = $stm->fetchScalars();

echo json_encode(
  array("projects" => get_projects($projectsData, $projects, $object))
);
