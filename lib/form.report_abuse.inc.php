<?php

$offender = HTTPContext::getInteger('offender');
$offense_subject = $_REQUEST['offense_subject'];
$offending_text = $_REQUEST['offending_text'];
$returnTo = $_REQUEST['returnTo'];
$buttonClicked = isset($_REQUEST['report_btn']);
$extra_details = $_REQUEST['extra_details'];

echo "<div class='page'>";
show_title("<CANTR REPLACE NAME=report_abuse>");
echo "<table>\n<tr>\n";
$required_data = isset($offender) && !empty($offense_subject) && !empty($offending_text);
if ($buttonClicked) {
  if ($required_data) {
    $playerInfo = Request::getInstance()->getPlayer();

    try {
      $reportTarget = Character::loadById($offender);
    } catch (InvalidArgumentException $e) {
      CError::throwRedirectTag("player", "error_too_far_away");
    }
    $played_by = "Played by: " . $reportTarget->getPlayer() . "\n";

    $mail_subject = $offense_subject . ' Abuse Report';
    $mail_contents = "Reporting player: $player\nOffending character: " . $reportTarget->getName()
      . " (id: $offender)\n $played_by Offending text: \"" . urldecode($offending_text)
      . "\"\n\nExtra details (optional):\n\n" . stripslashes($extra_details);

    $mailService = new MailService($playerInfo->getFullName(), $GLOBALS['emailSupport'], $playerInfo->getEmail());
    $mailSuccess = $mailService->sendPlaintext($GLOBALS['emailSupport'], $mail_subject, $mail_contents);

    if ($mailSuccess) {
      echo "<td><CANTR REPLACE NAME=report_abuse_success></td>";
    } else {
      echo "<td><CANTR REPLACE NAME=report_abuse_failed></td>";
    }
  } else {
    echo "<td>The form is missing essential data so it couldn't be sent.</td>";
  }
  echo "<td align='right' width=150><a href=\"index.php?page=$returnTo\">[<CANTR REPLACE NAME=button_char_go_back>]</a></td>";//displayed in any case
} else {
  //the form hasn't been sent
  if ($required_data) {
    //All necessary variables are known - display the form
    echo "<td colspan=2>\n";
    echo "<form method=post action=\"index.php?page=reportabuse&offender=$offender\">\n";
    echo "<input type='hidden' name='offense_subject' value='$offense_subject'>\n";
    echo "<input type='hidden' name='offending_text' value='$offending_text'>\n";//Passed through, shown to the user below (I didn't use a textbox since readonly boxes can still gain cursor focus, which is confusing when you can't write inside, and content in disabled boxes wouldn't get sent
    echo "<input type='hidden' name='returnTo' value='$returnTo'>\n";//Passed through to the next screen
    echo "<p><CANTR REPLACE NAME=report_abuse_text></p>\n";//"Here's a copy of the text you're about to report:"
    echo "<p>\"" . urldecode($offending_text) . "\"</p></td></tr>\n";
    echo "<tr><td colspan=2><p><CANTR REPLACE NAME=report_abuse_details></p>";//"You can write extra details in the box below..."
    echo "<p><textarea name='extra_details' rows='4' style='width: 100%'></textarea></p></td></tr>";
    echo "<tr><td align='left'><input type=submit CLASS=\"button_charmenu\" name='report_btn' value=\"<CANTR REPLACE NAME=report_abuse>\"></td><td align='right'><a href=\"index.php?page=$returnTo\">[<CANTR REPLACE NAME=contact_button_cancel>]</a></td></form>";
  } else echo "<td>The form is missing essential data and thus cannot be displayed.</td><td align='right' width=150><a href=\"index.php?page=$returnTo\">[<CANTR REPLACE NAME=button_char_go_back>]</a></td>";//This text should only appear if someone tries to enter the page without clicking a report link, so I see no need to translate it
}
echo "</tr></table></div>";
