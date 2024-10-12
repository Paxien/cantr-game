<?php

$db = Db::get();

$sessionExists = Session::sessionExistsInDatabase($s);

$sessionPlayer = Request::getInstance()->getPlayerId();

if ($sessionExists) {
  switch (PlayerSettings::getInstance($sessionPlayer)->get(PlayerSettings::EXIT_PAGE)) {
    case 1:
      $nextpage = TagBuilder::forTag("intro_wiki_language_link")->build()->interpret();
      break;
    case 2:
      $nextpage = "http://forum.cantr.org/";
      break;
    case 0: // should be webzine
    default:
    case 3:
      $nextpage = "/$lang_abr/";
      break;
  }

  //Get real IP address if available

  $headers = emu_getallheaders();

  if (isset($headers["Client-IP"])) {
    $client_ip = $headers["Client-IP"];
  }
  if (isset($headers["X-Forward-For"])) {
    $client_ip = $headers["X-Forward-For"];
  }

  $date = date("Y-m-d H:i:s");

  if (isset($client_ip)) {
    $stm = $db->prepare("SELECT COUNT(*) FROM ips WHERE player = :playerId AND client_ip = :clientIp");
    $stm->bindInt("playerId", $sessionPlayer);
    $stm->bindStr("clientIp", $client_ip);
    if ($stm->executeScalar() > 0) {
      $stm = $db->prepare("UPDATE ips SET endtime = :date WHERE player = :playerId AND client_ip = :clientIp");
      $stm->bindStr("date", $date);
      $stm->bindInt("playerId", $sessionPlayer);
      $stm->bindStr("clientIp", $client_ip);
      $stm->execute();
    }
  } else {
    $remaddr = $_SERVER['REMOTE_ADDR'];
    $stm = $db->prepare("SELECT COUNT(*) FROM ips WHERE player = :playerId AND ip = :ip");
    $stm->bindInt("playerId", $sessionPlayer);
    $stm->bindStr("ip", $remaddr);
    if ($stm->executeScalar() > 0) {
      $stm = $db->prepare("UPDATE ips SET endtime = :date WHERE player = :playerId AND ip = :ip");
      $stm->bindStr("date", $date);
      $stm->bindInt("playerId", $sessionPlayer);
      $stm->bindStr("ip", $remaddr);
      $stm->execute();
    }
  }
}

Session::deleteSessionFromDatabase($s);
session::deleteCookie();

cantr_redirect($nextpage);
