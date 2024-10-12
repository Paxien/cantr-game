<?php

// SANITIZE INPUT
$id = HTTPContext::getInteger('id');

$type = HTTPContext::getInteger('type');

$move_sign = HTTPContext::getInteger('move_sign');
$new_sign_position = HTTPContext::getInteger('new_sign_position');
$remove_sign = HTTPContext::getInteger('remove_sign');
$change_sign = HTTPContext::getInteger('insert_sign_position');

$project_data = $_REQUEST['project_data'];

if ($type == NamingConstants::SIGN_CHANGE) {
  $change_sign = HTTPContext::getInteger('change_sign');
}

$changetext = strip_tags($_REQUEST['changetext']);
$newsigntext = strip_tags($_REQUEST['newsigntext']);

$db = Db::get();

try {
  $targetLocation = Location::loadById($id);
  $charLocation = Location::loadById($char->getLocation());
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.buildings", "error_too_far_away");
}

$stm = $db->prepare("SELECT COUNT(*) FROM charnaming WHERE observer = :charId AND observed = :observed AND type = :type LIMIT 1");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("observed", $id);
$stm->bindInt("type", NamingConstants::TYPE_LOCATION);
$alreadyNamed = $stm->executeScalar();

if ($alreadyNamed) {
  $stm = $db->prepare("SELECT name,0 as type FROM charnaming WHERE observer = :charId AND observed = :observed AND type = :type LIMIT 1");
  $stm->bindInt("charId", $char->getId());
  $stm->bindInt("observed", $id);
  $stm->bindInt("type", NamingConstants::TYPE_LOCATION);
  $stm->execute();
} else {
  $stm = $db->prepare("SELECT name,type FROM locations WHERE id = :id LIMIT 1");
  $stm->bindInt("id", $id);
  $stm->execute();
}

$locinfo = $stm->fetchObject();

$signs_present = false;

$signs_alterable = false;

if ($locinfo->type != LocationConstants::TYPE_OUTSIDE) {

  $stm = $db->prepare("SELECT * FROM signs WHERE location = :locationId ORDER by signorder");
  $stm->bindInt("locationId", $id);
  $stm->execute();
  $existingSigns = $stm->fetchAll();

  if (count($existingSigns) > 0) {
    $firstSign = $existingSigns[0];

    if (!$alreadyNamed) {
      $locinfo->name = $firstSign->name;
    }

    $signs_present = true;

    $stm = $db->prepare("SELECT region FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $id);
    $loc_region = $stm->executeScalar();

    if (($charLocation->getId() != $id) and ($charLocation->getId() != $loc_region) and ($charLocation->getRegion() != $id)) {
      $signs_present = false; // Can't see signs when not there
    }
    if ($charLocation->getId() == $loc_region) { //can only alter signs if parent location
      $signs_alterable = true;
    }

    $stm = $db->prepare("SELECT loyal_to FROM animal_domesticated WHERE from_location = :locationId");
    $stm->bindInt("locationId", $charLocation->getId());
    $owner = $stm->executeScalar();
    if ($owner > 0) {
      $signs_alterable = false;
    }
  }
}

if ($signs_present && $loc_region > 0) {
  $signProject = Project::locatedIn($loc_region)->type(ProjectConstants::TYPE_ALTERING_SIGN)->subtype($id)->find();
}

$stm = $db->prepare("SELECT COUNT(o.id) FROM objects as o, objecttypes as t
  WHERE o.person = :charId AND o.type = t.id AND t.rules LIKE '%signwriting%'");
$stm->bindInt("charId", $char->getId());
$signwriting_ok = $stm->executeScalar() > 0;


if ($project_data) {

  if ($loc_region == 0) {
    $err_msg = "<CANTR REPLACE NAME=error_signs_signwriting_not_possible_whilst_travelling>";
  }

  if ($signProject !== null) {
    $err_msg = "<CANTR REPLACE NAME=error_signs_sign_project_already_exists>";
  }

  if (!$signs_present) {
    $err_msg = "<CANTR REPLACE NAME=error_signs_no_signs_are_visible>";
  }

  if (!$signwriting_ok) {
    $err_msg = "<CANTR REPLACE NAME=error_signs_no_signwriting_tools>";
  }
  if (($signwriting_ok) && (!$signs_alterable) && ($signs_present)) {
    $err_msg = "<CANTR REPLACE NAME=error_sign_cannot_be_altered_here>";
  }
  if (!$err_msg) {
    $location_name = urlencode("<CANTR LOCNAME ID=$id>");
    switch ($type) {

      case NamingConstants::SIGN_CHANGE :
        if (empty(trim($changetext))) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_text_was_entered>";
        }
        if (!$change_sign) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
        }

        if (!$err_msg) {
          $stm = $db->prepare("SELECT * FROM signs WHERE location = :locationId AND signorder = :signorder LIMIT 1");
          $stm->bindInt("locationId", $id);
          $stm->bindInt("signorder", $change_sign);
          $stm->execute();

          if ($target_info = $stm->fetchObject()) {
            if ($target_info->name == $changetext) {
              $err_msg = "<CANTR REPLACE NAME=error_signs_new_sign_text_is_not_different>";
            }
          } else {
            $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
          }
        }
        if (!$err_msg) {
          $result = "$type:$change_sign:" . urlencode(htmlspecialchars($changetext));
          $name = "<CANTR REPLACE NAME=activity_altersign_change_sign SIGN=$change_sign PLACE=$location_name>";
        }
        break;
      case NamingConstants::SIGN_REMOVE:
        if (!$remove_sign) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
        }
        if (count($existingSigns) == 1) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_only_one_sign_cant_be_removed>";
        }
        if (!$err_msg) {
          $stm = $db->prepare("SELECT * FROM signs WHERE location = :locationId AND signorder = :signorder LIMIT 1");
          $stm->bindInt("locationId", $id);
          $stm->bindInt("signorder", $remove_sign);
          $stm->execute();

          if ($target_info = $stm->fetchObject()) {
          } else {
            $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
          }
        }
        if (!$err_msg) {
          $result = "$type:$remove_sign:0";
          $name = "<CANTR REPLACE NAME=activity_altersign_remove_sign SIGN=$remove_sign PLACE=$location_name>";
        }

        break;
      case NamingConstants::SIGN_MOVE:
        if (!$move_sign) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
        }

        if ((!$new_sign_position) || ($new_sign_position == $move_sign)) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_new_position_was_selected>";
        }
        if (count($existingSigns) == 1) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_only_one_sign_cant_be_removed>";
        }
        if (!$err_msg) {
          $stm = $db->prepare("SELECT * FROM signs WHERE location = :locationId AND signorder = :signorder LIMIT 1");
          $stm->bindInt("locationId", $id);
          $stm->bindInt("signorder", $move_sign);
          $stm->execute();

          if ($target_info = $stm->fetchObject()) {
          } else {
            $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_was_selected_as_target>";
          }
        }
        if (!$err_msg) {
          $stm = $db->prepare("SELECT * FROM signs WHERE location = :locationId AND signorder = :signorder LIMIT 1");
          $stm->bindInt("locationId", $id);
          $stm->bindInt("signorder", $new_sign_position);
          $stm->execute();

          if ($target_info = $stm->fetchObject()) {
          } else {
            $err_msg = "<CANTR REPLACE NAME=error_signs_no_new_position_was_selected> ";
          }
        }
        if (!$err_msg) {
          $result = "$type:$move_sign:$new_sign_position";
          $name = "<CANTR REPLACE NAME=activity_altersign_move_sign SIGN=$move_sign TO=$new_sign_position PLACE=$location_name>";
        }

        break;
      case NamingConstants::SIGN_ADD:
        if (empty(trim($newsigntext))) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_sign_text_was_entered>";
        }
        if (!isset($insert_sign_position)) {
          $err_msg = "<CANTR REPLACE NAME=error_signs_no_new_position_was_selected> ";
        }

        if (!$err_msg) {
          $result = "$type:$insert_sign_position:" . urlencode(htmlspecialchars($newsigntext));
          $name = "<CANTR REPLACE NAME=activity_altersign_add_sign SIGN=$insert_sign_position PLACE=$location_name>";
        }
        break;
      default :
        $err_msg = "<CANTR REPLACE NAME=error_signs_unknown_sign_project_specified>";
    }
  }
  if ($err_msg) {
    CError::throwRedirect("char.buildings", $err_msg);
  }

    $turnsleft = floor(2 * ProjectConstants::DEFAULT_PROGRESS_PER_DAY);
    $general = new ProjectGeneral($name, $char->getId(), $char->getLocation());
    $type = new ProjectType(ProjectConstants::TYPE_ALTERING_SIGN, $id, StateConstants::NONE, ProjectConstants::PROGRESS_MANUAL, ProjectConstants::PARTICIPANTS_NO_LIMIT, ProjectConstants::DIGGING_SLOTS_NOT_USE);
    $requirement = new ProjectRequirement($turnsleft, 'days:2');
    $output = new ProjectOutput(0, $result, 0);

    $project = new Project($general, $type, $requirement, $output);
    $project->saveInDb();

    if (!$char->isBusy()) {
      $char->setProject($project->getId());
      $char->saveInDb();
    }

    Event::create(213, "PLACE=$location_name")->forCharacter($char)->show();
    Event::create(214, "ACTOR=$character PLACE=$location_name")->inLocation($loc_region)->except($char)->show();
    Event::create(215, "PLACE=$location_name")->inLocation($id)->show();

    redirect("char.events");
} else {
  if ($locinfo->name != '') {
    $oldname = $locinfo->name;
    if (strstr($oldname, "<")) {
      $oldname = TagBuilder::forText($oldname)->observedBy($char)->allowHtml(false)->build()->interpret();
    }
  } else {
    $oldname = TagBuilder::forTag("unnamed_location")->observedBy($char)->allowHtml(false)->build()->interpret();
  }

  $oldname_enc = urlencode($oldname);

  $smarty = new CantrSmarty();
  $smarty->assign("oldname_enc", $oldname_enc);
  $smarty->assign("oldname", $oldname);
  $smarty->assign("id", $id);

  $smarty->assign("signs", $existingSigns);
  $smarty->assign("signwriting_ok", $signwriting_ok);
  $smarty->assign("signs_alterable", $signs_alterable);
  $smarty->assign("sign_project", $signProject);
  $smarty->assign("signs_present", $signs_present);
  $smarty->assign("lastpage", "char.buildings");
  $smarty->displayLang("page.nameloc.tpl", $lang_abr);
}