<?php
// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$amount = HTTPContext::getInteger('amount');
$object_id = HTTPContext::getInteger('object_id');
$data = $_REQUEST['data'];
$material = $_REQUEST['material'];

// Loading information

try {
  $coinPress = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_coin_press_not_same_location");
}

// Standard checks

$message = "";

if (!$char->isInSameLocationAs($coinPress)) {
  CError::throwRedirectTag("char.objects", "error_coin_press_not_same_location");
}

if ($coinPress->getType() != ObjectConstants::TYPE_COIN_PRESS) {
  $message = "<CANTR REPLACE NAME=error_not_coin_press>";
} else if ($coinPress->isInUse()) {

  $message = "<CANTR REPLACE NAME=error_coin_press_in_use>";
} else if ($data && $amount <= 0) {

  $message = "<CANTR REPLACE NAME=error_coin_press_illegal_amount MAX_AMOUNT=" . _MAX_AMOUNT_COINS . ">";
} else if ($data && $amount > _MAX_AMOUNT_COINS) {

  $message = "<CANTR REPLACE NAME=error_coin_press_illegal_amount MAX_AMOUNT=" . _MAX_AMOUNT_COINS . ">";
} else if ($data && ($material < 1 || $material > 12)) {

  $message = "<CANTR REPLACE NAME=error_coin_press_illegal_material>";
}

if ($message == "") {

  // Check ok, continue

  if ($data) {

    // We have data, continue processing

    // Determine type of coin
    switch ($material) {

    case 1 :
      $material_name = 'gold';
      $material_object = 413;
      break;
    case 2 :
      $material_name = 'silver';
      $material_object = 414;
      break;
    case 3 :
      $material_name = 'copper';
      $material_object = 415;
      break;
    case 4 :
      $material_name = 'nickel';
      $material_object = 416;
      break;
    case 5 :
      $material_name = 'iron';
      $material_object = 417;
      break;
    case 6 :
      $material_name = 'steel';
      $material_object = 418;
      break;
    case 7 :
      $material_name = 'bronze';
      $material_object = 576;
      break;
    case 8 :
      $material_name = 'platinum';
      $material_object = 577;
      break;
    case 9 :
      $material_name = 'tin';
      $material_object = 578;
      break;
    case 10 :
      $material_name = 'chromium';
      $material_object = 579;
      break;
    case 11 :
      $material_name = 'cobalt';
      $material_object = 580;
      break;
    case 12 :
      $material_name = 'aluminium';
      $material_object = 808;
      break;
    }

    // Set requirements of project
    $turns = $amount / _AMOUNT_COINS_PER_DAY * ProjectConstants::DEFAULT_PROGRESS_PER_DAY;
    $amount_needed = $amount * _GRAMS_PER_COIN;
    $req = "raws:$material_name>$amount_needed";

    // Make necessary changes in database
    $coinPress->setTypeid(1);
    $coinPress->saveInDb();

    $projectName = "Producing $amount $material_name coins";
    $generalSub = new ProjectGeneral($projectName, $char->getId(), $char->getLocation());
    $typeSub = new ProjectType(ProjectConstants::TYPE_PRODUCING_COINS, $object_id, 0, 0, _MAX_PARTICIPANTS, 0);
    $requirementSub = new ProjectRequirement($turns, $req);
    $outputSub = new ProjectOutput(0, "$material_object:$amount");

    $project = new Project($generalSub, $typeSub, $requirementSub, $outputSub);
    $project->saveInDb();


    redirect("char.inventory");
  } else {

    // We have no data, present form

    show_title("<CANTR REPLACE NAME=title_use_coin_press>");

    echo "<div class='page'>";
    echo "<TABLE>";

    echo "<FORM METHOD=post ACTION=\"index.php?page=usecoinpress\">";

    echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=coin_press_description MAX_AMOUNT=" . _MAX_AMOUNT_COINS . "></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_amount>:</TD>";
    echo "<TD><INPUT TYPE=text NAME=amount></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_material>:</TD>";
    echo "<TD>";
    echo "<INPUT TYPE=radio NAME=material VALUE=1><CANTR REPLACE NAME=raw_gold><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=2><CANTR REPLACE NAME=raw_silver><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=3><CANTR REPLACE NAME=raw_copper><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=4><CANTR REPLACE NAME=raw_nickel><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=5><CANTR REPLACE NAME=raw_iron><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=6><CANTR REPLACE NAME=raw_steel><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=7><CANTR REPLACE NAME=raw_bronze><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=8><CANTR REPLACE NAME=raw_platinum><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=9><CANTR REPLACE NAME=raw_tin><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=10><CANTR REPLACE NAME=raw_chromium><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=11><CANTR REPLACE NAME=raw_cobalt><BR>";
    echo "<INPUT TYPE=radio NAME=material VALUE=12><CANTR REPLACE NAME=raw_aluminium><BR>";
    echo "</TD></TR>";
    echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
    echo "<INPUT TYPE=hidden NAME=object_id VALUE=$object_id>";
    echo "<TR><TD COLSPAN=2 ALIGN=center><INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_continue>\"></TD></TR>";

    echo "</FORM>";

    echo "</TABLE>";
    echo "</div>";
  }
} else {
  CError::throwRedirect("char.objects", $message);
}
