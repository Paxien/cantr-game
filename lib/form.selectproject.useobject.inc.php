<?php

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');

function need_object($objectName, $reqleft) {

  $parts = Parser::rulesToArray($reqleft);
  if (isset($parts['objects'])) {
    $objects = Parser::rulesToArray($parts['objects'], ",>");
    return isset($objects[$objectName]) && $objects[$objectName] > 0;
  }
  return false;
}

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if (!$char->hasWithinReach($object)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$smarty = new CantrSmarty;

$smarty->assign ("ITEM", $object->getName());
$smarty->assign ("object_id", $object->getId());

$db = Db::get();
$stm = $db->prepare("SELECT * FROM projects WHERE location = :locationId ORDER BY name");
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
foreach ($stm->fetchAll() as $project_info) {
  if (need_object($object->getName(), $project_info->reqleft)) {
    
    $project = new stdClass();
    $project->id = $project_info->id;
    $project->name = $project_info->name;
    $project->day = $project_info->init_day;
    $project->turn = $project_info->init_turn;
    $project->char = NULL;

    try {
      $initiator = Character::loadById($project_info->initiator);
      if ($initiator->isAlive() && $initiator->isInSameLocationAs($char)) {
        $tag = new tag;
        $tag->language = $char->getLanguage();
        $tag->character = $character;
        $tag->html = false;
        $tag->content = "<CANTR CHARNAME ID=$project_info->initiator>";
        $project->char = $tag->interpret ();
      }
    } catch (InvalidArgumentException $e) {
      // ignore
    }

    $projects[] = $project;
  }
}

$smarty->assign ("projects", $projects);
$smarty->displayLang ("form.selectproject.tpl", $lang_abr);
