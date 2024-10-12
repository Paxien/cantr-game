<?php 


$toolId = HTTPContext::getInteger('object_id');
$description = $_REQUEST['description'];

if ($char->getLocation() == 0) {
  CError::throwRedirectTag("char.inventory", "error_not_while_travel");
}

try {
  $tool = CObject::loadById($toolId);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}
if (!$char->hasInInventory($tool)) {
  CError::throwRedirectTag("char.inventory", "error_too_far_away");
}

import_lib("func.rules.inc.php");
if (!$tool->hasAccessToAction("changeobjdesc")) {
  CError::throwRedirectTag("char.inventory", "error_not_describable");
}

$describableObjects = array();
$inInventory = array();

$db = Db::get();
$stm = $db->prepare("SELECT o.id, ot.rules, (o.person > 0) AS inInventory FROM objects o
    INNER JOIN objecttypes ot ON ot.id = o.type AND ot.rules LIKE '%describable%'
  WHERE (o.person = :charId) OR (o.location = :locationId)
  ORDER BY inInventory DESC, o.id DESC");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("locationId", $char->getLocation());
$stm->execute();
// list of object which is describable by tool $toolId
foreach ($stm->fetchAll() as $object) {
  
  $typeRules = Parser::rulesToArray($object->rules);
  if (isset($typeRules['describable'])) {
    $describable = Parser::rulesToArray($typeRules['describable'], ",>");
    if (isset($describable['bytool'])) {
      $toolsList = explode("/", $describable['bytool']);
      if (in_array($tool->getName(), $toolsList)) {
        $describableObjects[] = $object->id;
        $inInventory[$object->id] = $object->inInventory;
      }
    }
  }
}

// getting current descriptions of all listed objects
$describableObjects = Descriptions::getDescriptionsArray($describableObjects, Descriptions::TYPE_OBJECT);
$objects = array(true => array(), false => array());
foreach ($describableObjects as $objId => $oldDesc) {
  $objects[$inInventory[$objId]][] = array("id" => $objId, "oldDesc" => $oldDesc);
}

$smarty = new CantrSmarty();
$smarty->assign("objects", $objects);
$smarty->assign("tool_id", $toolId);
$smarty->assign("DESC_MAX_LEN", Descriptions::$TEXT_MAXLEN[Descriptions::TYPE_OBJECT]);
$smarty->displayLang("page.change_object_description.tpl", $lang_abr);
