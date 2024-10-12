<?php

$data = HTTPContext::getRawString("data");
$subject = HTTPContext::getRawString("subject");
$message = HTTPContext::getRawString("message");

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MAIL_ALL_PLAYERS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

$db = Db::get();

  show_title ("<CANTR REPLACE NAME=title_mail_all>");

  if ($data) {
    $stm = $db->prepare("SELECT email FROM players WHERE status < :locked");
    $stm->bindInt("locked", PlayerConstants::LOCKED);
    $stm->execute();
    foreach ($stm->fetchScalars() as $playerEmail) {

      mail ("$playerEmail","$subject","$message","From: Cantr Players Department <".$GLOBALS['emailSupport'].">");
    }

    redirect("player");
  } else {

    echo "<CENTER><TABLE WIDTH=700><TR><TD>";

    echo "<CANTR REPLACE NAME=page_mail_all_1><BR><BR>";

    echo "<FORM METHOD=post ACTION=\"index.php?page=mailall\"><CENTER>";
    echo "<INPUT TYPE=text NAME=subject SIZE=60><BR>";
    echo "<TEXTAREA NAME=message COLS=50 ROWS=20></TEXTAREA><BR><BR>";
    echo "<INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_continue>\">";
    echo "<INPUT TYPE=hidden NAME=data VALUE=yes>";
    echo "</FORM>";

    echo "<BR><BR><A HREF=\"index.php?page=player\"><CANTR REPLACE NAME=back_to_player></A></CENTER>";
    echo "</TD></TR></TABLE></CENTER>";
  }
