<?php
include_once "func.genes.inc.php";
// SANITIZE INPUT

$object_d = HTTPContext::getInteger('object_id', null);
$ocharacter = HTTPContext::getInteger('ocharacter', null);
$goal = HTTPContext::getInteger('goal', null);
$error = new CError;
$accepted = true;
$nextaction = "choose goal";

$db = Db::get();
if (isset($ocharacter) && !empty($ocharacter)) {
  $stm = $db->prepare("SELECT id,location,status,project FROM chars WHERE id = :charId LIMIT 1");
  $stm->bindInt("charId", $ocharacter);
  $stm->execute();
  $victim_info = $stm->fetchObject();
  if ($accepted) {
    if ($victim_info->status > CharacterConstants::CHAR_ACTIVE) {
      $error->message = "<CANTR REPLACE NAME=error_target_dead_char>";
      $accepted = false;
    }
  }
}

if (isset($object_d) && !empty($object_d)) {
  $stm = $db->prepare("SELECT id,location FROM objects WHERE id = :objectId LIMIT 1");
  $stm->bindInt("objectId", $object_d);
  $stm->execute();
  $victim_info = $stm->fetchObject();
}

$perpetrator_loc = new char_location($char->getId());
/*** CHECK WHETHER PERPETRATOR IS ALREADY DRAGGING OR WORKING ON A PROJECT ***/
if ($char->getProject() != 0) {
  if (isset($ocharacter) && !empty($ocharacter)) {
    $error->message = "<CANTR REPLACE NAME=error_drag_working_on_project>";
  }
  if (isset($object_d) && !empty($object_d)) {
    $error->message = "<CANTR REPLACE NAME=error_pull_working_on_project>";
  }
  $accepted = false;
}

if ($char->isDragging()) {
  if (isset($ocharacter) && !empty($ocharacter)) {
    $error->message = "<CANTR REPLACE NAME=error_drag_someone_else>";
  }
  if (isset($object_d) && !empty($object_d)) {
    $error->message = "<CANTR REPLACE NAME=error_pull_someone_else>";
  }
  $accepted = false;
}

/*** CHECK WHETHER VICTIM HAPPENS TO BE DRAGGED ALREADY ***/
if ((isset($object_d) && !empty($object_d)) || (isset($ocharacter) && !empty($ocharacter))) {
  if (isset($ocharacter)) {
    $draggingStm = $db->prepare("SELECT * FROM dragging WHERE victim = :victimCharId AND victimtype = 1 LIMIT 1");
    $draggingStm->bindInt("victimCharId", $ocharacter);
    $draggingStm->execute();
  }
  if (isset($object_d)) {
    $draggingStm = $db->prepare("SELECT * FROM dragging WHERE victim = :victimObjectId AND victimtype = 2 LIMIT 1");
    $draggingStm->bindInt("victimObjectId", $object_d);
    $draggingStm->execute();
  }
  if ($dragging_info = $draggingStm->fetchObject()) {
    if (isset($ocharacter)) {
      $tag = new tag();
      $tag->content = "<CANTR CHARNAME ID=$victim_info->id>";
      $tag->html = false;
      $drag = $tag->interpret();
      $error->message = "<CANTR REPLACE NAME=error_drag_person_already_drag DRAGGED=" . urlencode($drag) . " DRAGGER=";
    }
    if (isset($object_d)) {
      $error->message = "<CANTR REPLACE NAME=error_drag_object_already_drag DRAGGER=";
    }
    $stm = $db->prepare("SELECT * FROM draggers WHERE dragging_id = :id");
    $stm->bindInt("id", $dragging_info->id);
    $stm->execute();

    $draggers = [];
    foreach ($stm->fetchAll() as $draggers_id) {
      $line = TagBuilder::forChar($draggers_id->dragger)->observedBy($char)->allowHtml(false)->build()->interpret();
      $draggers[] = $line;
    }
    $error->message .= urlencode(implode(", ", $draggers)) . ">";
    $accepted = false;
  }
}
/*** CHECK WHETHER THE VICTIM IS CLOSEBY ENOUGH ***/

if ($char->getLocation() != $victim_info->location) {
  if (isset($ocharacter) && !empty($ocharacter)) {
    $error->message = "<CANTR REPLACE NAME=error_drag_person_not_here>";
  }
  if (isset($object_d) && !empty($object_d)) {
    $error->message = "<CANTR REPLACE NAME=error_drag_object_not_here>";
  }
  $accepted = false;
}
/*** CHECK WHETHER TRYING TO DRAG TO TRAVELLING ***/
if (isset($goal)) {
  if ($goal == 0) {
    $error->message = "<CANTR REPLACE NAME=error_drag_not_travelling>";
    $accepted = false;
  }
}
/*** CHECK WHETHER IT IS A LEGAL DRAG GOAL ***/
if (isset($goal)) {
  if (!$goal) {
    $error->message = "<CANTR REPLACE NAME=error_illegal_destination>";
    $accepted = false;
  }
}

/*** CHECK OBJECT IS DRAGGABLE ***/
if (isset($object_d) && !empty($object_d) && $accepted) {
  $stm = $db->prepare("SELECT setting FROM objects WHERE id = :objectId LIMIT 1");
  $stm->bindInt("objectId", $object_d);
  $objectSetting = $stm->executeScalar();
  $accepted = in_array($objectSetting, [ObjectConstants::SETTING_PORTABLE, ObjectConstants::SETTING_QUANTITY]);
  if (!$accepted) {
    $error->message = "<CANTR REPLACE NAME=error_drag_not_draggable>";
  }
}

/*** IN CASE NO PROBLEMS: CONTINUE ***/
if ($accepted) {
  if (true) {
    show_title("SELECT A GOAL");
    echo "<div class=\"page\">";
    echo "<TABLE>";
    echo "<TR><TD WIDTH=200><FORM METHOD=POST ACTION=\"index.php?s=$s&page=drag&data=yes\">";
    if (isset($ocharacter)) {
      $tag = new tag();
      $tag->content = "<CANTR CHARNAME ID=$victim_info->id>";
      $tag->html = false;
      $line = $tag->interpret();
      echo "Goal to drag $line to:</TD><TD><SELECT NAME=goal style='width:100%'>";
      if ($victim_info->project) {
        echo "<OPTION VALUE=-1><CANTR REPLACE NAME=drag_from_project>";
      }
    }
    if (isset($object_d)) {
      echo "Goal to pull object to:</TD><TD><SELECT NAME=goal style='width:100%'>";
    }
    if ($perpetrator_loc->islocation) {
      $stm = $db->prepare("SELECT id FROM locations WHERE region = :regionId AND type != 1 ORDER BY name");
      $stm->bindInt("regionId", $char->getLocation());
      $stm->execute();
    } else {
      $stm = $db->prepare("SELECT id FROM locations WHERE (region = :regionId OR id = :locationId) AND id != 0 ORDER BY name");
      $stm->bindInt("regionId", $char->getLocation());
      $stm->bindInt("locationId", $perpetrator_loc->region);
      $stm->execute();
    }
    $targetPaces = array();
    foreach ($stm->fetchScalars() as $locationId) {
      $locName = TagBuilder::forLocation($locationId)->observedBy($char)->allowHtml(false)->build()->interpret();
      if ($locName == "") {
        $locName = "unnamed location";
      }
      $targetPaces[$locationId] = $locName;
    }
    asort($targetPaces);
    foreach ($targetPaces as $id => $name) {
      echo "<option value=$id>$name</option>";
    }
    echo "</SELECT>";
    if (isset($object_d)) {
      $object = CObject::loadById($object_d);
      if ($object->isQuantity()) {
        echo "<input type=number name=amount value=" . $object->getAmount() . "><br>";
      }
    }
    echo "<INPUT TYPE=hidden NAME=character VALUE=$character>";
    if (isset($ocharacter) && !empty($ocharacter)) {
      echo "<INPUT TYPE=hidden NAME=ocharacter VALUE=$ocharacter>";
    }
    if (isset($object_d) && !empty($object_d)) {
      echo "<INPUT TYPE=hidden NAME=object_id VALUE=$object_d>";
    }
    echo "<INPUT TYPE=hidden NAME=nextaction VALUE=\"drag\">";
    echo "</TD></TR><TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=Next>";
    echo "</FORM></TR></TABLE></div>";
  }
} else {
  $error->page = "char";
  $error->report();
}
