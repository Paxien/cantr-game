<?php
// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');
$freq = HTTPContext::getInteger('freq');
$data = $_REQUEST['data'];

$db = Db::get();

$radio = CObject::loadById($object_id);

if ($char->getLocation() == 0 || !$char->isInSameLocationAs($radio)) {
  $error_message = "<CANTR REPLACE NAME=error_radio_other_location>";
} else if ((strpos($radio->getRules(),"radio_receiver")===false) && (strpos($radio->getRules(),"rebroadcast")===false)) {
  $error_message = "<CANTR REPLACE NAME=error_not_a_radio_or_repeater>";
} else if (!isset($data)) {

  show_title ("<CANTR REPLACE NAME=title_radio_freq>");

  echo "<FORM METHOD=post ACTION=\"index.php?page=setfreq\">";
  echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
  echo "<input type=\"hidden\" name=\"object_id\" value=\"$object_id\">";
  echo "<div class='page'>";
  echo "<TABLE>";

  echo "<TR><TD><CANTR REPLACE NAME=form_radio_frequency>:<BR>";
  echo "<CANTR REPLACE NAME=form_radio_frequency_info></TD>";
  echo "<TD><INPUT TYPE=text NAME=freq VALUE=\"{$radio->getSpecifics()}\"></TD></TR>";
  // button_submit: Submit
  echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_submit>\"></TD></TR>";
  echo "</TABLE></div>";
  echo "</FORM>";
} elseif ($freq < 100 || $freq > 300) {
  $error_message = "<CANTR REPLACE NAME=error_radio_wrong_freq>";
} else {

  $radio->setSpecifics($freq);
  $radio->saveInDb();
  $stm = $db->prepare("UPDATE radios SET frequency = :frequency WHERE item = :objectId");
  $stm->bindInt("frequency", $freq);
  $stm->bindInt("objectId", $object_id);
  $stm->execute();

  Event::create(159, "RADIO=$object_id FREQ=$freq")->forCharacter($char)->show();
  Event::create(160, "RADIO=$object_id ACTOR=$character")->nearCharacter($char)->except($char)->show();

  redirect("char");
}

if (isset($error_message)) {
  CError::throwRedirect("char.objects", $error_message);
}
