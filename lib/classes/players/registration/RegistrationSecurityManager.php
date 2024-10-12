<?php

class RegistrationSecurityManager
{
  private $newPlayer;

  const RESTRICT_NONE = 1;
  const RESTRICT_SUSPICIOUS = 2;
  const RESTRICT_ALL = 3;

  public function __construct($newPlayer)
  {
    $this->newPlayer = $newPlayer;
  }

  public function performSecurityMeasures()
  {
    $db = Db::get();

    $globalConfig = new GlobalConfig($db);
    $protectionLevel = $globalConfig->getIntroProtectionLevel();

    if (empty($protectionLevel)) {
      $protectionLevel = self::RESTRICT_NONE;
    }

    // matching
    $matcher = new ForIntroPlayerMatching($this->newPlayer);
    $matches = $matcher->findSuspiciousMatches();

    if ($protectionLevel != self::RESTRICT_ALL && count($matches) == 0) { // no suspicious matches, so nothing to do
      return;
    }

    // there are matches, so they should be visible on pendingplayers page
    $suspiciousMatches = "Suspicious because of matches ";
    $suspiciousMatches .= "<a class='show' href='javascript:void(0);'
      data-show='{$this->newPlayer->id}'>(show " . count($matches) . ")</a>:\n";
    $suspiciousMatches .= "<p class='toshow' id='toshow_{$this->newPlayer->id}'>";
    foreach ($matches as $matchingPlr) {
      $color = $this->getColor($matchingPlr->type);
      $suspiciousMatches .= "<span style='color:$color'> * ";
      $suspiciousMatches .= "$matchingPlr->firstname $matchingPlr->lastname | ";
      $suspiciousMatches .= "[$matchingPlr->email] $matchingPlr->lastlogin ";
      $suspiciousMatches .= "($matchingPlr->id)</span>\n";
    }
    $suspiciousMatches .= "</p>";

    $stm = $db->prepare("UPDATE newplayers SET research = CONCAT(:value, research) WHERE id = :id");
    $stm->bindStr("value", "$suspiciousMatches\n");
    $stm->bindInt("id", $this->newPlayer->id);
    $stm->execute();

    if ($protectionLevel == self::RESTRICT_NONE) {
      return;
    } elseif ((count($matches) > 1) || ($protectionLevel == self::RESTRICT_ALL)) {
      // suspicious registration should get locked until accepted by PD

      $env = Request::getInstance()->getEnvironment();
      $lockedMessage = $env->introExists() ? "SUSPICIOUS (LOCKED ON INTRO)" : "SUSPICIOUS";
      $formattedMessage = "<b style=\"font-size:16pt;color:#f00\">$lockedMessage</b> - ";
      $stm = $db->prepare("UPDATE newplayers SET research = CONCAT(:value, research)
        WHERE id = :id");
      $stm->bindStr("value", $formattedMessage);
      $stm->bindInt("id", $this->newPlayer->id);
      $stm->execute();
    }
  }

  private function getColor($type)
  {
    switch ($type) {
      case "locked":
        return "yellow";
      case "refused":
        return "red";
      case "pending":
      default:
        return "white";
    }
  }

} 
