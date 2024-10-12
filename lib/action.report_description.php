<?php

$character = HTTPContext::getInteger('character');
$return = $_REQUEST['return'];
$desc_id = HTTPContext::getInteger('desc_id');
$reason = $_REQUEST['reason'];

$descriptionText = Descriptions::getRawDescriptionById($desc_id);

if (!$descriptionText) {
  CError::throwRedirectTag("char.events", "error_report_wrong_description");
}

$reporter = Request::getInstance()->getPlayer();

$authorChar = Descriptions::getDescriptionAuthorById($desc_id);

$db = Db::get();

$stm = $db->prepare("SELECT name, player FROM chars WHERE id = :charId");
$stm->bindInt("charId", $authorChar);
$stm->execute();
list ($authorName, $authorPlayer) = $stm->fetch(PDO::FETCH_NUM);

$offenderPlayer = Player::loadById($authorPlayer);

$mailContents = "Reporting player: $player\n
Offender: $authorName (id: $authorChar) played by {$offenderPlayer->getFullNameWithId()}\n
Offending text: '$descriptionText' (description id: $desc_id)\n
Extra details (optional): '$reason'";

$mailService = new MailService($reporter->getFullName(), $GLOBALS['emailSupport'], $reporter->getEmail());
$mailSuccess = $mailService->sendPlaintext($GLOBALS['emailSupport'], "Custom description abuse report", $mailContents);

// report success/failure
show_title ("<CANTR REPLACE NAME=report_abuse>");
echo "<div style=\"margin:auto;width:700px;text-align:center;\">";
if ($mailSuccess) {
  echo "<CANTR REPLACE NAME=report_abuse_success>";
} else {
  echo "<CANTR REPLACE NAME=report_abuse_failed>";
}
echo "<br><br>";
echo "<form method=\"post\" action=\"index.php?page=$return\"><input type=\"submit\" class=\"button_charmenu\" value=\"<CANTR REPLACE NAME=button_char_go_back>\" /></form>";
echo "</div>";
