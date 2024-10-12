<?php

// SANITIZE INPUT
$id = HTTPContext::getInteger('id');

$db = Db::get();

try {
  $body = Character::loadById($id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

if ($body->getStatus() <= CharacterConstants::CHAR_ACTIVE) {
  CError::throwRedirectTag("char.events", "error_cannot_loot_living");
}

if (!$char->isInSameLocationAs($body)) {
  CError::throwRedirectTag("char.events", "error_too_far_away");
}

$smarty = new CantrSmarty;

$characterInfoView = new CharacterInfoView($char);
$characterInfoView->show();

if ($body->getDeathDate() > 0)
  if ($body->getDeathDate() < $turn->day - 20)
    $smarty->assign("bodyage", 1);
  elseif ($body->getDeathDate() < $turn->day - 10)
    $smarty->assign("bodyage", 2);
  else
    $smarty->assign("bodyage", 3);

$smarty->assign("cause", $body->getDeathCause());
switch ($body->getDeathCause()) {
  case CharacterConstants::CHAR_DEATH_VIOLENCE:
    if ($body->getDeathWeapon() > 0) {
      $uniqueName = ObjectType::loadById($body->getDeathWeapon())->getUniqueName();
      $wpn = "item_{$uniqueName}_o";
    } else {
      $wpn = "weapon_bare_fist";
    }

    $tag = new tag;
    $tag->language = $l;
    $tag->content = "<CANTR REPLACE NAME=$wpn>";
    $smarty->assign("WEAPON", $tag->interpret());

    break;
  case CharacterConstants::CHAR_DEATH_ANIMAL:
    $stm = $db->prepare("SELECT name FROM animal_types WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $body->getDeathWeapon());
    $animalName = $stm->executeScalar();
    $animalName = str_replace(" ", "_", $animalName);
    $animal = "<CANTR REPLACE NAME=animal_" . urlencode($animalName) . "_o>";

    $smarty->assign("ANIMAL", $animal);
    break;
}

$stm = $db->prepare("SELECT clothtype.unique_name, cloth.id FROM objects cloth, objecttypes clothtype, objectcategories oc " .
  "WHERE cloth.person = :charId AND clothtype.id = cloth.type AND clothtype.objectcategory = oc.id AND oc.parent= :category ORDER BY cloth.type");
$stm->bindInt("charId", $id);
$stm->bindInt("category", ObjectConstants::OBJCAT_CLOTHES);
$stm->execute();
$clothes = $stm->fetchAll();
foreach ($clothes as $clothing) {
  $clothing->name = "<CANTR REPLACE NAME=item_" . $clothing->unique_name . "_o>";
  $clothing->description = Descriptions::getDescription($clothing->id, Descriptions::TYPE_OBJECT);
  if (empty($clothing->description)) {
    $clothing->description = "<CANTR REPLACE NAME=cloth_desc_" . $clothing->unique_name . ">";
  }
}

$smarty->assign("clothes", $clothes);
$smarty->assign("id", $id);

$stm = $db->prepare("SELECT name, description FROM charnaming WHERE observer=:observer AND observed= :observed AND type=1");
$stm->bindInt("observer", $char->getId());
$stm->bindInt("observed", $id);
$stm->execute();
if ($charname_info = $stm->fetchObject()) {

  //in database in NAME we had "<CANTR CHARDESC>" in plain text and others special chars in html entieties. let normalize it.
  $charname_info->name = str_replace("<CANTR CHARDESC>", htmlentities("<CANTR CHARDESC>"), $charname_info->name);

  $name = $charname_info->name;
  $description = html_entity_decode(str_replace("<br />", "\n", $charname_info->description));
} else {
  $description = "";
  $name = "";
}

// we search for the dead character's description
$stm = $db->prepare
(
  "SELECT custom_desc
  FROM chars
  WHERE id = :observed"
  );
$stm->bindInt("observed", $id);
$stm->execute();
$charname_desc = $stm->fetchObject();
$charDescription = $charname_desc->custom_desc;

$smarty->assign("bodyDescription", $charDescription);
$smarty->assign("bodyName", $name);
$smarty->assign("bodyPersonalDescription", $description);


$smarty->displayLang("form.lootbody.tpl", $lang_abr);

$bottomMenus = new BottomMenus($char);
$bottomMenus->show();
