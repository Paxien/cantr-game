<?php

$redObjectId = HTTPContext::getInteger('object_id');

$db = Db::get();
$stm = $db->prepare("SELECT setting FROM objects WHERE id = :id");
$stm->bindInt("id", $redObjectId);
$objectSetting = $stm->executeScalar();

$isQuantityObject = ($objectSetting == ObjectConstants::SETTING_QUANTITY);

if ($isQuantityObject && !isset($_REQUEST['amount'])) {
  include _LIB_LOC ."/form.take.php";
} else {
  include _LIB_LOC ."/action.take.php";
}
