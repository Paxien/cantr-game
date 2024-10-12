<?php
require_once("func.radios.inc.php");
require_once("func.strip_html_tags.inc.php");

// SANITIZE INPUT
$character = HTTPContext::getInteger('character');
$object_id = HTTPContext::getInteger('object_id');
$freq = HTTPContext::getInteger('freq');
$data = $_REQUEST['data'];
$msg = $_REQUEST['msg'];

$db = Db::get();

if (Limitations::getLims($player, Limitations::TYPE_PLAYER_RADIO_USAGE) > 0) {
  CError::throwRedirectTag("char.objects", "error_using_radio_disallowed");
}

try {
  $object = CObject::loadById($object_id);
} catch (InvalidArgumentException $e) {
  CError::throwRedirectTag("char.objects", "error_too_far_away");
}

if ($char->getLocation() != $object->getLocation() || $char->getLocation() == 0) {

  // error_radio_other_location: You are not on the same location as this radio.
  $error_message = "<CANTR REPLACE NAME=error_radio_other_location>";
} else {
  if (!preg_match('/broadcast=(\d+)/', $object->getRules(), $matches)) {

    // error_not_a_radio: This is not a radio broadcaster.
    $error_message = "<CANTR REPLACE NAME=error_not_a_radio>";
  } else {

    // Implementation assumes that different kind of broadcasters can be made,
    // with different powers. Basic initial version should be around 50 or less.
    // (Keep in mind, average road length is 28)
    $distance = $matches[1];

    if (!isset($data)) {

      // title_radio: USE RADIO
      show_title("<CANTR REPLACE NAME=title_radio>");

      echo "<FORM METHOD=post ACTION=\"index.php?page=radio\">";
      echo "<input type=\"hidden\" name=\"object_id\" value=\"$object_id\">";
      echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
      echo "<div class='page'>";
      echo "<table>";
      // form_radio_frequency: Frequency
      // form_radio_frequency_info: You can select any value between 100 and 300.
      echo "<TR><TD><CANTR REPLACE NAME=form_radio_frequency>:<BR>";
      echo "<CANTR REPLACE NAME=form_radio_frequency_info></TD>";
      echo "<TD><INPUT TYPE=text NAME=freq VALUE=\"{$object->getSpecifics()}\" SIZE=53></TD></TR>";

      // form_radio_message: Enter your message
      echo "<TR><TD><CANTR REPLACE NAME=form_radio_message>:</TD>";

      echo '<TD><TEXTAREA NAME=msg cols=40 rows=7></TEXTAREA></TD></TR>';

      // button_submit: Submit
      echo "<TR><TD COLSPAN=2 ALIGN=center><BR><INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_submit>\"></TD></TR>";
      echo "</TABLE>";
      echo "</div>";
      echo "</FORM>";
    } else {
      if ($freq < 100 || $freq > 300) {

        // error_radio_wrong_freq: You entered an illegal frequency (only between 100 and 300 is allowed).
        $error_message = "<CANTR REPLACE NAME=error_radio_wrong_freq>";
      } else {
        if ($msg == "") {

          // error_radio_empty_message: You entered an empty message for your broadcast.
          $error_message = "<CANTR REPLACE NAME=error_radio_empty_message>";
        } else {

          $msg = strip_tags($msg);
          $msg = stripslashes($msg);
          $msg = TextFormat::withoutNewlines($msg);
          $msg = urlencode($msg);

          $object->setSpecifics((string)$freq);
          $object->saveInDb();
          $stm = $db->prepare("UPDATE radios SET frequency = :frequency WHERE item = :objectId LIMIT 1");
          $stm->bindInt("frequency", $freq);
          $stm->bindInt("objectId", $object_id);
          $stm->execute();

          Event::create(155, "ACTOR=$character MESSAGE=$msg RADIO=$object_id FREQ=$freq")
            ->nearCharacter($char)->except($char)->show();

          // event_156: You say into radio <CANTR OBJNAME ID=#RADIO# TYPE=1>: "#MESSAGE#"
          Event::create(156, "MESSAGE=$msg RADIO=$object_id FREQ=$freq ACTOR=$character")
            ->forCharacter($char)->show();


          $stm = $db->prepare("SELECT x, y FROM locations WHERE id = :locationId LIMIT 1");
          $stm->bindInt("locationId", $char->getLocation());
          $stm->execute();
          $loc_info = $stm->fetchObject();
          $receivers = getlisteners($freq, $loc_info->x, $loc_info->y, $distance);
          if ($receivers) {
            $stm = $db->prepareWithIntList("SELECT objects.location FROM objects WHERE id in (:ids)", [
              "ids" => $receivers,
            ]);
            $stm->execute();
            // event_154: You hear from radio at freq. #FREQ#: "#MESSAGE#"

            $locations = $stm->fetchScalars();
            $observers = array();
            if (!empty($locations)) {
              $stm = $db->prepareWithIntList("SELECT id FROM chars WHERE location IN (:locations) AND status <= :active", [
                "locations" => $locations,
              ]);
              $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
              $stm->execute();
              $observers = $stm->fetchScalars();
              Event::create(154, "MESSAGE=$msg FREQ=$freq")->forCharacters($observers)->show();
            }
          }
          redirect("char");
        }
      }
    }
  }
}

if (isset($error_message)) {
  CError::throwRedirect("char.objects", $error_message);
}
