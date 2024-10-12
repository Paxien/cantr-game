<?php
session_start();


$accepted = true;

$message = $_POST['message'];
$mail_subject = $_POST['mail_subject'];

$errmsg = "";

$mail_from = "";
$mail_confirm = "";
$mail_to = intval($_POST["email_to"]);

$req = Request::getInstance();
$db = Db::get();

if ($req->isPlayerSpecified()) { // logged in
  $loggedin = true;
  $playerInfo = $req->getPlayer();

  if (!check_cr($playerInfo->getEmail()) || !Validation::isEmailValid($playerInfo->getEmail())) {
    $accepted = false;
    $errmsg .= "<CANTR REPLACE NAME=error_contact_mail_misformed_or_blank><br />";
  }
  $mail_from = $playerInfo->getEmail();
} else {

  $mail_from = $_POST["mail_from"];
  $mail_confirm = $_POST["mail_confirm"];
  $recaptchaResponse = $_POST['g-recaptcha-response'];

  $loggedin = false;

  $recaptcha = new \ReCaptcha\ReCaptcha(_RECAPTCHA_SECRET_KEY);
  $resp = $recaptcha->verify($recaptchaResponse, $_SERVER['REMOTE_ADDR']);
  if (!$resp->isSuccess()) {
    $accepted = false;
    $errmsg .= "<CANTR REPLACE NAME=error_contact_captcha_mismatch><br />";
  }
  if ((!check_cr($mail_from)) || !Validation::isEmailValid($mail_from)) {
    $accepted = false;
    $errmsg .= "<CANTR REPLACE NAME=error_contact_mail_misformed_or_blank><br />";
  }
  if ($mail_from != $mail_confirm) {
    $accepted = false;
    $errmsg .= "<CANTR REPLACE NAME=error_contact_mail_mismatch><br />";
  }
}

if (!check_cr($mail_subject)) {
  $accepted = false;
  $errmsg .= "<CANTR REPLACE NAME=error_contact_subject_misformed_or_blank><br />";
}

if (trim($message) == "") {
  $accepted = false;
  $errmsg .= "<CANTR REPLACE NAME=error_contact_blank_message><br />";
}

if ($accepted) {
  list($username, $domain) = explode("@", $mail_from);
  if (!getmxrr($domain, $mxrecords)) {
    $accepted = false;
    $errmsg .= "<CANTR REPLACE NAME=error_contact_unrecognised_email_domain><br />";
  }
}

$client_ip = $_SERVER['REMOTE_ADDR'];

$headers = emu_getallheaders();

if (isset($headers["Client-IP"])) {
  $client_ip = $headers["Client-IP"];
}

if (isset($headers["X-Forwarded-For"])) {
  $client_ip = $headers["X-Forwarded-For"];
}

if ($client_ip) {
  $curr_time = time();
  $wait = 5; // 5 minutes between emails
  $test_time = $curr_time - $wait * 60; # seconds

  $stm = $db->prepare("DELETE FROM contactips WHERE time <= :time and banned = 0");
  $stm->bindInt("time", $test_time);
  $stm->execute();
  if ($loggedin) {
    $wait = 1; // 1 minute between emails
    $test_time = $curr_time - $wait * 60;
  }
  $stm = $db->prepare("SELECT COUNT(*) FROM contactips WHERE ip = :ip AND (time >= :time OR banned = 1)");
  $stm->bindStr("ip", $client_ip);
  $stm->bindInt("time", $test_time);
  $wait_period = $stm->executeScalar();
  if ($wait_period) {
    $errmsg .= "<CANTR REPLACE NAME=error_contact_wait_minutes MINUTES=$wait><br />";
    $accepted = false;
  }
} else {
  //no ip so throw out ! ?
  redirect("intro");
}

if ($accepted) {
  $stm = $db->prepare("SELECT COUNT(*) FROM contactips WHERE ip = :ip LIMIT 1");
  $stm->bindStr("ip", $client_ip);
  $exists_record = $stm->executeScalar() > 0;
  if ($exists_record) {
    $stm = $db->prepare("UPDATE contactips SET time = :time WHERE ip = :ip");
    $stm->bindStr("ip", $client_ip);
    $stm->bindInt("time", $curr_time);
    $stm->execute();
  } else {
    $stm = $db->prepare("INSERT INTO contactips (ip, time, banned) VALUES (:ip, :time, 0) ");
    $stm->bindStr("ip", $client_ip);
    $stm->bindInt("time", $curr_time);
    $stm->execute();
  }
  // build message
  if ($mail_to == -1) { //support@cantr.net
    $dept_info = new stdClass();
    $dept_info->id = -1;
    $dept_info->email = $GLOBALS['emailPlayers'] . "," . $GLOBALS['emailProgramming'];
    $dept_info->name = "Support Department";
  } else {
    switch ($mail_to) {
      case 100 :
        $dept_info = new stdClass();
        $dept_info->id = 100;
        $dept_info->email = $GLOBALS['emailCommunications'];
        $dept_info->name = "Webzine";
        break;
      case 101 :
        $dept_info = new stdClass();
        $dept_info->id = 101;
        $dept_info->email = $GLOBALS['emailFinances'];
        $dept_info->name = "Finance";
        break;
      default:
        $stm = $db->prepare("SELECT * FROM councils WHERE id = :id LIMIT 1");
        $stm->bindInt("id", $mail_to);
        $stm->execute();
        $dept_info = $stm->fetchObject();
        $dept_info->email = $dept_info->email . "@cantr.org";
        break;
    }
  }
  if (isset($dept_info->id)) {
    $tag = new tag;
    $tag->language = $l;
    $dept = urlencode($dept_info->name);
    $tag->content = "<CANTR REPLACE NAME=contact_message_head DEPT=$dept>";
    $messagehead = $tag->interpret();
    $messagehead = urldecode($messagehead) . ":...\n";

    $stm = $db->prepare("SELECT name FROM languages WHERE id = :language");
    $stm->bindInt("language", $l);
    $langName = $stm->executeScalar();
    if (!$langName) {
      $langName = "unknown";
    }

    if ($loggedin) {
      $playerName = $playerInfo->getFullName();

      $messagehead2 = "Player id: " . $playerInfo->getId() . "\n" .
        "Player Name: $playerName\n" .
        "Email: $mail_from\n" .
        "Player Language: $langName\n" .
        "Client IP: $client_ip\n" .
        "Message Sent to $dept_info->name\n\n";
    } else {
      $playerName = "Unregistered player";

      $messagehead2 = "Player id: Anonymous\n" .
        "Email: $mail_from\n" .
        "Client IP: $client_ip\n" .
        "Player language: $langName\n" .
        "Message Sent to $dept_info->name\n\n";

    }

    $message1 = $messagehead2 . $message;
    $message2 = $messagehead . $message;

    $toMailingList = new MailService($playerName, $GLOBALS['emailSender'], $mail_from . "," . $dept_info->email . "," . $GLOBALS['emailSupport']);
    $toMailingList->sendPlaintext($dept_info->email . "," . $GLOBALS['emailSupport'], "CCM:" . $mail_subject, $message1);

    $mailCopy = new MailService("Cantr Support", $GLOBALS['emailSender'], $dept_info->email . "," . $GLOBALS['emailSupport']);
    $mailCopy->sendPlaintext($mail_from, $mail_subject, $message2);

    $stm = $db->prepare("INSERT INTO accepted_emails (address) VALUES (:email)");
    $stm->bindStr("email", $mail_from);
    $stm->execute();
  }

  redirect("contact", ["mail_sent" => 1]);

} else {
  unset($data);
  $_SESSION["message"] = $message;
  $_SESSION["mail_from"] = $mail_from;
  $_SESSION["mail_confirm"] = $mail_confirm;
  $_SESSION["mail_subject"] = $mail_subject;
  $_SESSION["mail_to"] = $mail_to;
  $_SESSION["errmsg"] = $errmsg;

  redirect("contact", ["reenter" => 1]);
}

function check_cr($string)
{
  // tests for mailheader injection no crlf allowed in public headers
  $teststr = urldecode($string);
  if ((preg_match("/\r/i", $teststr)) || (preg_match("/\n/i", $teststr)) || (strlen(trim($teststr)) == 0)) {
    return false;
  }
  return true;
}
