<?php

// SANITIZE INPUT
$title = $_REQUEST['title'];

$db = Db::get();
$stm = $db->prepare("INSERT INTO obj_notes (title,utf8title,converted,encoding,setting) VALUES (NULL , :title,2,'utf8',0)");
$stm->bindStr("title", $title);
$stm->execute();

$noteid = $db->lastInsertId();

ObjectCreator::inInventory($char, ObjectConstants::TYPE_ENVELOPE,
  ObjectConstants::SETTING_PORTABLE, 0)->typeid($noteid)->create();

redirect("char.inventory");

