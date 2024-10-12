<?php

// SANITIZE INPUT
$object_id = HTTPContext::getInteger("object_id");
$description = $_REQUEST["description"];
$data = $_REQUEST["data"];

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("player", "error_too_far_away");
}

if (!$char->hasInInventory($object)) {
  CError::throwRedirectTag("player", "error_too_far_away");
}

$rules = Parser::rulesToArray($object->getRules());
$isDescribable = false;
if (isset($rules["describable"])) {
  $descRules = Parser::rulesToArray($rules["describable"], ",>");
  if (isset($descRules["bylabel"])) {
    $isDescribable = $descRules["bylabel"] == "yes";
  }
}

if (!$isDescribable) {
  CError::throwRedirectTag("char.inventory", "error_not_describable");
}


if ($data) {
  $accepted = Descriptions::setDescription($object_id, Descriptions::TYPE_OBJECT, $description, $character);
  if (!$accepted) {
    CError::throwRedirectTag("char.inventory", "error_description_disallowed");
  } else {
    redirect("char.inventory");
  }
} else  {
  $smarty = new CantrSmarty;

  $oldName = Descriptions::getDescription($object_id, Descriptions::TYPE_OBJECT);
  
  $smarty->assign ("OLDNAME", $oldName);
  $smarty->assign ("DESC_MAX_LEN", Descriptions::$TEXT_MAXLEN[Descriptions::TYPE_OBJECT]);
  $smarty->assign ("object_id", $object_id);

  $smarty->displayLang ("page.namekey.tpl", $lang_abr); 
}
