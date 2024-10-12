<?php

// SANITIZE INPUT
$object = HTTPContext::getInteger('object');

$db = Db::get();

$stm = $db->prepare("SELECT * FROM objecttypes WHERE id = :id");
$stm->bindInt("id", $object);
$stm->execute();
$objecttype_info = $stm->fetchObject();

// CUT

$smarty = new CantrSmarty();
$smarty->assign("objecttype_info", $objecttype_info);
$smarty->assign("object", $object);

$stm = $db->query("SELECT id,name FROM state_types ORDER BY name");
$skills = [0 => "None"];
foreach ($stm->fetchAll() as $skillType) {
  $skills[$skillType->id] = $skillType->name;
}

$smarty->assign("skills", $skills);

$stm = $db->prepare("SELECT * FROM obj_properties WHERE objecttype_id = :id");
$stm->bindInt("id", $objecttype_info->id);
$stm->execute();
$props = $stm->fetchAll();

$props[] = (object)["property_type" => "", "details" => ""];

$smarty->assign("props", $props);

$stm = $db->query("SELECT oc.id, IF(ocparent.id IS NOT NULL, CONCAT(ocparent.name, ' -&gt; ', oc.name), oc.name) AS name
  FROM objectcategories oc LEFT JOIN objectcategories ocparent ON (oc.parent = ocparent.id) ORDER BY name");

$smarty->assign("objectcategories", $stm->fetchAll());

if ($objecttype_info->image_file_name == "") {
  if (is_file(_IMAGES_OBJECTS_REL . "object_0_blank_en.png")) {
    $image = "<IMG src=" . _IMAGES_OBJECTS . "object_0_blank_en.png>";
  } else {
    $image = "<IMG src=" . _IMAGES_OBJECTS . "noimage.png>";
  }
} else {
  $image = "<IMG src=" . _IMAGES_OBJECTS . strtolower($objecttype_info->image_file_name) . ">";
}

$smarty->assign("image", $image);

$smarty->displayLang("admin/mo_form.tpl", $lang_abr);