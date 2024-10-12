<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger('object_id');

function need_resource($resourceName, $reqleft) {

  $parts = Parser::rulesToArray($reqleft);
  if (isset($parts['raws'])) {
    $raws = Parser::rulesToArray($parts['raws'], ",>");
    return isset($raws[$resourceName]) && $raws[$resourceName] > 0;
  }
  return false;
}

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

if ($object->getType() != ObjectConstants::TYPE_RAW) {
  CError::throwRedirect("char.inventory", "It's not raw material");
}

if (!$char->hasWithinReach($object)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

$resource = new Resource($object);

$smarty = new CantrSmarty;

$smarty->assign ("ITEM", $resource->getName());
$smarty->assign ("object_id", $object_id);

$db = Db::get();
$stm = $db->prepare("SELECT * FROM projects WHERE location = :locationId ORDER BY name");
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
foreach ($stm->fetchAll() as $project_info) {
  if (need_resource($resource->getName(), $project_info->reqleft)) {

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
