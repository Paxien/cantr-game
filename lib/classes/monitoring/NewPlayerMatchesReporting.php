<?php

class NewPlayerMatchesReporting
{
  const MAX_PARTIAL_EMAIL_MATCHES = 10;
  /** @var user */
  private $user;

  /** @var Db */
  private $db;

  public function __construct(user $user, Db $db)
  {
    $this->user = $user;
    $this->db = $db;
  }

  public function findMatchesWithPlayersDatabase($currentPlayerId, $remaddr)
  {
    $messageLines = [];
    $messageLines[] = "<CANTR REPLACE NAME=page_accept_same_name>:";
    list($playerExactNameMessageLines, $exactNameMatches) = $this->getAllExactNameMatches($currentPlayerId);
    $playerPartialNameMessageLines = $this->getAllPartialNameMatches($currentPlayerId);

    $messageLines = array_merge($messageLines, $playerExactNameMessageLines, $playerPartialNameMessageLines);

    $messageLines[] = "\n<CANTR REPLACE NAME=page_accept_same_ip>:";
    list($playerIpMessageLines, $playerExactIpMatches) =
      $this->getPlayerExactIpMatches($currentPlayerId, $remaddr, $exactNameMatches);
    list($removedPlayerMessageLines, $removedPlayersExactIpMatches) =
      $this->getRemovedPlayerExactIpMatches($remaddr, $exactNameMatches);

    $messageLines = array_merge($messageLines, $playerIpMessageLines, $removedPlayerMessageLines);

    preg_match("/(\d{1,3}[.]){3}/", $remaddr, $ippart);

    $messageLines[] = "\n<CANTR REPLACE NAME=page_accept_same_ip_part> ($ippart[0]*):";
    list($playerPartialIpLines, $playerPartialIpMatches) =
      $this->getPlayerPartialIpMatches($currentPlayerId, $ippart[0], $playerExactIpMatches, $exactNameMatches);
    list($removedPlayerPartialIpLines, $removedPlayerPartialIpMatches) =
      $this->getRemovedPlayerPartialIpMatches($currentPlayerId, $ippart[0], $exactNameMatches);

    $messageLines = array_merge($messageLines, $playerPartialIpLines, $removedPlayerPartialIpLines);

    $CEIP = $this->formCantrExplorerReport(
      $playerExactIpMatches, $removedPlayersExactIpMatches,
      $playerPartialIpMatches, $removedPlayerPartialIpMatches
    );

    $messageLines[] = "\n<CANTR REPLACE NAME=page_accept_same_email>:";
    $exactEmailMessageLines = $this->getAllExactEmailMatches($currentPlayerId, $exactNameMatches);
    $messageLines = array_merge($messageLines, $exactEmailMessageLines);

    $messageLines[] = "\n<CANTR REPLACE NAME=page_accept_same_email_part>:";
    $partialEmailMessageLines = $this->getAllPartialEmailMatches($currentPlayerId, $exactNameMatches);
    $messageLines = array_merge($messageLines, $partialEmailMessageLines);

    $messageLines = implode("\n", $messageLines);
    return [$messageLines, $CEIP];
  }

  private function formCantrExplorerReport(...$args)
  {
    $idLists = [];
    foreach ($args as $arg) {
      $idLists[] = implode(",", $arg);
    }
    return implode("|", $idLists);
  }

  /**
   * @param $currentPlayerId
   * @return array tuple [$messageLines, $exactNameMatches]
   */
  private function getAllExactNameMatches($currentPlayerId)
  {
    $exactNameMatches = [];
    $messageLines = [];

    // EXACT NAME MATCHES from players
    $stm = $this->db->prepare("SELECT *
      FROM players
      WHERE (firstname = :firstName AND firstname != '')
        AND (lastname = :lastName AND lastname != '')
        AND id != :id
      LIMIT 25");
    $stm->bindStr("firstName", $this->user->getFirstName());
    $stm->bindStr("lastName", $this->user->getLastName());
    $stm->bindInt("id", $currentPlayerId);
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $exactNameMatches[] = $playerInfo->id;
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }

    // EXACT NAME MATCHES FROM removed_players
    $stm = $this->db->prepare("SELECT *
      FROM removed_players
      WHERE (firstname = :firstName AND firstname != '')
        AND (lastname = :lastName AND lastname != '')
      LIMIT 25");

    $stm->bindStr("firstName", $this->user->getFirstName());
    $stm->bindStr("lastName", $this->user->getLastName());
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      if (strpos($playerInfo->reason, "REMOVED") !== false) {
        $playerInfo->status = PlayerConstants::REMOVED;
      } else {
        $playerInfo->status = PlayerConstants::REFUSED;
      }
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }

    return [$messageLines, $exactNameMatches];
  }


  /**
   * @param $currentPlayerId
   * @return array
   */
  private function getAllPartialNameMatches($currentPlayerId)
  {
    $messageLines = [];
    // PARTIAL NAME MATCHES FROM players
    $stm = $this->db->prepare("SELECT *
      FROM players
      WHERE ((firstname = :firstName AND firstname != '')
        OR (lastname = :lastName AND lastname != ''))
        AND NOT (firstname = :firstName AND lastname = :lastName)
        AND id != :id
      LIMIT 25");

    $stm->bindStr("firstName", $this->user->getFirstName());
    $stm->bindStr("lastName", $this->user->getLastName());
    $stm->bindInt("id", $currentPlayerId);
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $messageLines[] = $this->printPlayer($playerInfo, false);
    }

    // PARTIAL NAME MATCHES FROM removed_players
    $stm = $this->db->prepare("SELECT *
      FROM removed_players
      WHERE (firstname = :firstName AND firstname != '')
        AND (lastname = :lastName AND lastname != '')
        AND NOT (firstname = :firstName AND lastname = :lastName)
      LIMIT 25");
    $stm->bindStr("firstName", $this->user->getFirstName());
    $stm->bindStr("lastName", $this->user->getLastName());
    $stm->bindInt("id", $currentPlayerId);
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $playerInfo->status = PlayerConstants::REMOVED;
      $messageLines[] = $this->printPlayer($playerInfo, false);
    }
    return $messageLines;
  }

  /**
   * @param $currentPlayerId
   * @param $remaddr
   * @param $exactNameMatches
   * @return array [$messageLines, $exactPlayerIpMatches]
   */
  private function getPlayerExactIpMatches($currentPlayerId, $remaddr, $exactNameMatches)
  {
    $messageLines = [];
    $exactPlayerIpMatches = [];
    // EXACT MATCH ON IP FROM players, ips
    $stm = $this->db->prepare("
      SELECT ips.lasttime AS ip_lasttime, ips.ip AS ip, players.*
        FROM players, ips
          WHERE ips.ip LIKE :remoteAddress AND ips.player = players.id
            AND players.id != :id
      UNION
      SELECT ips.lasttime AS ip_lasttime, ips.ip AS ip, players.*
        FROM players, ips
          WHERE ips.client_ip LIKE :remoteAddress AND ips.player = players.id
            AND players.id != :id
      LIMIT 25");
    $stm->bindInt("id", $currentPlayerId);
    $stm->bindStr("remoteAddress", "$remaddr%");
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $exactPlayerIpMatches[] = $playerInfo->id;
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }
    return [$messageLines, $exactPlayerIpMatches];
  }

  /**
   * @param $remaddr
   * @param $exactNameMatches
   * @return array [$messageLines, $exactRemovedPlayerIpMatches]
   */
  private function getRemovedPlayerExactIpMatches($remaddr, $exactNameMatches)
  {
    $messageLines = [];
    $exactRemovedPlayerIpMatches = [];
    // EXACT MATCH ON IP FROM removed_players
    $stm = $this->db->prepare("SELECT * FROM removed_players WHERE lastlogin LIKE :remoteAddr LIMIT 25");
    $stm->bindStr("remoteAddr", "%$remaddr%");
    foreach ($stm->fetchAll() as $playerInfo) {
      $exactRemovedPlayerIpMatches[] = $playerInfo->id;
      $playerInfo->status = PlayerConstants::REMOVED;
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }
    return [$messageLines, $exactRemovedPlayerIpMatches];
  }

  /**
   * @param $currentPlayerId
   * @param $ipPart
   * @param $exactIpMatches
   * @param $exactNameMatches
   * @return array [$messageLines, $partialIpMatches]
   */
  private function getPlayerPartialIpMatches($currentPlayerId, $ipPart, $exactIpMatches, $exactNameMatches)
  {
    $messageLines = [];
    $partialIpMatches = [];
    // PARTIAL MATCH ON IP FROM players, ips
    $stm = $this->db->prepare("
        SELECT MAX(ips.lasttime) AS ip_lasttime, ips.ip AS ip, players.*
          FROM players, ips
          WHERE ips.ip LIKE :ipPart AND ips.player = players.id
            AND players.id != :id
          GROUP BY players.id
      UNION
        SELECT MAX(ips.lasttime) AS ip_lasttime, ips.client_ip AS ip, players.*
          FROM players, ips
          WHERE ips.client_ip LIKE :ipPart AND ips.player = players.id
            AND players.id != :id
          GROUP BY players.id LIMIT 25
    ");
    $stm->bindInt("id", $currentPlayerId);
    $stm->bindStr("ipPart", "$ipPart%");
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      if (!in_array($playerInfo->id, $exactIpMatches)) {
        $partialIpMatches[] = $playerInfo->id;
        $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
      }
    }
    return [$messageLines, $partialIpMatches];
  }

  /**
   * @param $currentPlayerId
   * @param $firstIpPart
   * @param $exactIpMatches
   * @param $exactNameMatches
   * @return array
   */
  private function getNewPlayerPartialIpMatches($currentPlayerId, $firstIpPart, $exactIpMatches, $exactNameMatches)
  {
    $messageLines = [];
    $partialIpMatches = [];
    // PARTIAL MATCH ON IP FROM newplayers
    $stm = $this->db->prepare("SELECT * FROM newplayers WHERE ipinfo LIKE :firstIpPart AND id != :id LIMIT 25");
    $stm->bindInt("id", $currentPlayerId);
    $stm->bindStr("firstIpPart", "%$firstIpPart%");
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      if (!in_array($playerInfo->id, $exactIpMatches)) {
        $partialIpMatches[] = $playerInfo->id;
        $playerInfo->status = PlayerConstants::PENDING;
        $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
      }
    }
    return [$messageLines, $partialIpMatches];
  }

  /**
   * @param $currentPlayerId
   * @param $firstIpPart
   * @param $exactNameMatches
   * @return array
   */
  private function getRemovedPlayerPartialIpMatches($currentPlayerId, $firstIpPart, $exactNameMatches)
  {
    $messageLines = [];
    $partialIpMatches = [];
    // PARTIAL MATCH ON IP FROM removed_players
    $stm = $this->db->prepare("SELECT * FROM removed_players WHERE lastlogin LIKE :firstIpPart LIMIT 25");
    $stm->bindStr("firstIpPart", "%$firstIpPart%");
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $playerInfo->status = PlayerConstants::REMOVED;
      $partialIpMatches[] = $playerInfo->id;
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }
    return [$messageLines, $partialIpMatches];
  }

  /**
   * @param $currentPlayerId
   * @param $exactNameMatches
   * @return string[] list of messages to display
   */
  private function getAllExactEmailMatches($currentPlayerId, $exactNameMatches)
  {
    $messageLines = [];
    // EXACT MATCH ON EMAIL FROM players (it should never happen, since there is a signup check for this)
    $stm = $this->db->prepare("SELECT * FROM players WHERE email = :email AND id != :id LIMIT 25");
    $stm->bindStr("email", $this->user->getEmail());
    $stm->bindInt("id", $currentPlayerId);
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }

    // EXACT MATCH ON EMAIL FROM removed_players
    $stm = $this->db->prepare("SELECT * FROM removed_players WHERE email = :email LIMIT 25");
    $stm->bindStr("email", $this->user->getEmail());
    $stm->execute();
    foreach ($stm->fetchAll() as $playerInfo) {
      $playerInfo->status = PlayerConstants::REMOVED;
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }
    return $messageLines;
  }

  public function getAllPartialEmailMatches($currentPlayerId, $exactNameMatches)
  {
    $exactEmailAddress = $this->user->getEmail();
    $meaningfulEmailPart = $this->filterOutTrailingEmailPart($this->user->getEmail());
    $dotlessMeaningfulEmailPart = str_replace(".", "", $meaningfulEmailPart);

    $meaningfulEmailPrefix = $this->getSufficientEmailPrefix($meaningfulEmailPart);
    $dotlessMeaningfulEmailPrefix = $this->getSufficientEmailPrefix($dotlessMeaningfulEmailPart);

    $messageLines = [];
    // EXACT MATCH ON EMAIL FROM players (it should never happen, since there is a signup check for this)
    $stm = $this->db->prepare("SELECT * FROM players WHERE
      (email LIKE :emailPattern1 OR email LIKE :emailPattern2)
      AND email != :exactEmail AND id != :id LIMIT 25");
    $stm->bindStr("emailPattern1", $meaningfulEmailPrefix . "%");
    $stm->bindStr("emailPattern2", $dotlessMeaningfulEmailPrefix . "%");
    $stm->bindStr("exactEmail", $exactEmailAddress);
    $stm->bindInt("id", $currentPlayerId);
    $stm->execute();
    $partialEmailMatchPlayers = [];
    foreach ($stm->fetchAll() as $playerInfo) {
      $partialEmailMatchPlayers[] = $playerInfo;
    }

    // EXACT MATCH ON EMAIL FROM removed_players
    $stm = $this->db->prepare("SELECT * FROM removed_players WHERE
      (email LIKE :emailPattern1 AND email LIKE :emailPattern2)
      AND email != :exactEmail LIMIT 25");
    $stm->bindStr("emailPattern1", $meaningfulEmailPrefix . "%");
    $stm->bindStr("emailPattern2", $dotlessMeaningfulEmailPrefix . "%");
    $stm->bindStr("exactEmail", $exactEmailAddress);
    $stm->execute();

    foreach ($stm->fetchAll() as $playerInfo) {
      $playerInfo->status = PlayerConstants::REMOVED;
      $partialEmailMatchPlayers[] = $playerInfo;
    }

    $partialEmailMatchPlayers = $this->sortDescendingByAccuracy($partialEmailMatchPlayers,
      $meaningfulEmailPart, $dotlessMeaningfulEmailPart);

    foreach ($partialEmailMatchPlayers as $playerInfo) {
      $messageLines[] = $this->printPlayer($playerInfo, in_array($playerInfo->id, $exactNameMatches));
    }
    if (count($partialEmailMatchPlayers) == self::MAX_PARTIAL_EMAIL_MATCHES) {
      $messageLines[] = "(the list is not exhaustive)";
    }

    return $messageLines;
  }

  /**
   * Return a printable line of one player for the Pending Players page
   */
  private function printPlayer($player, $exact_name_match)
  {
    if ($player->ipinfo) {
      $player->lastlogin = $player->ipinfo;
    }
    if ($player->status == PlayerConstants::REMOVED && strpos($player->reason, "IDLED OUT ON INTRO SERVER") === 0) {
      $player->status = PlayerConstants::IDLEDOUT;
    }

    $ret = "<font color=\"" . $this->getFontColor($player->status, $exact_name_match) . "\">"
      . $player->firstname . " " . $player->lastname . " "
      . "[" . $player->email . "]";
    if ($player->ip_lasttime) {
      $ret .= " IP Match date: " . date("d/m/Y H:i", strtotime($player->ip_lasttime)) . " " .
        $player->ip . " (" . gethostbyaddr($player->ip) . ")";
    } else {
      $ret .= " last date: " . $player->lastlogin;
    }

    $ret .= " (";
    if ($player->status == PlayerConstants::REMOVED && strpos($player->reason, "REFUSED") === 0) {
      $player->status = PlayerConstants::REFUSED;
      $ret .= $player->reason;
    } elseif (strpos($player->reason, "IDLED OUT ON INTRO SERVER") === 0) {
      $ret .= $player->reason;
    } elseif ($player->status == PlayerConstants::REFUSED && !empty($player->notes)) {
      $ret .= explode("\n", $player->notes)[0];
    } else {
      $ret .= $this->printStatus($player->status);
    }
    $ret .= ")";

    //These three statuses doesn't have player objects, so no use in linking to them
    if (!in_array($player->status, [
        PlayerConstants::REMOVED,
        PlayerConstants::REFUSED,
        PlayerConstants::PENDING
      ])
    ) {
      $ret .= "(<a href=\"index.php?page=listplayers&player_id="
        . $player->id . "&data=yes&set=player\">" . $player->id . "</a>)";
    }
    $ret .= "</font>";
    return $ret;
  }

  /**
   * Return a readable status string for given status code
   * @param $status PlayerConstants one of constants
   * @return string
   */
  private function printStatus($status)
  {
    switch ($status) {
      case PlayerConstants::PENDING:
        return "**pending**";
      case PlayerConstants::APPROVED:
        return "approved";
      case PlayerConstants::ACTIVE:
        return "active";
      case PlayerConstants::LOCKED:
        return "locked";
      case PlayerConstants::REMOVED:
        return "removed";
      case PlayerConstants::UNSUBSCRIBED:
        return "unsubscribed";
      case PlayerConstants::IDLEDOUT:
        return "idled out";
      case PlayerConstants::REFUSED:
        return "refused";
      default:
        return "status unknown";
    }
  }

  /**
   * Return a readable color (HTML) based on status and exact name match.
   * The order of these if-statements dictates the order in which PD want
   * the different statuses displayed. In order of importance:
   * 1. locked account
   * 2. exact name match
   * 3. active/approved/pending accounts
   * 4. dead accounts
   * 5. refused/removed accounts
   */
  private function getFontColor($status, $exact_name_match)
  {
    if ($exact_name_match && $status != PlayerConstants::LOCKED) {
      return "orange";
    } else {
      switch ($status) {
        case PlayerConstants::LOCKED:
          return "yellow";
        case PlayerConstants::REMOVED:
        case PlayerConstants::REFUSED:
          return "red";
        case PlayerConstants::PENDING:
        case PlayerConstants::APPROVED:
        case PlayerConstants::ACTIVE:
          return "white";
        case PlayerConstants::UNSUBSCRIBED:
        case PlayerConstants::IDLEDOUT:
          return "cyan";
        default:
          return "grey";
      }
    }
  }

  public static function filterOutTrailingEmailPart($emailAddress)
  {
    $emailAddress = trim($emailAddress);
    // part before "@"
    $userName = explode("@", $emailAddress)[0];

    // part before "+", because some services treat part after "+" as a tag for the same email
    $userName = explode("+", $userName)[0];

    preg_match("/^(.*)?[0-9_-]*$/", $userName, $matches);
    $userNameWithoutTrailingNumbers = $matches[1];

    return $userNameWithoutTrailingNumbers;
  }

  public static function getSufficientEmailPrefix($emailPart)
  {
    return mb_substr($emailPart, 0, min(4, mb_strlen($emailPart)));
  }

  /**
   * @param $partialEmailMatches
   * @param $meaningfulEmailPart
   * @param $dotlessMeaningfulEmailPart
   * @return array
   */
  private function sortDescendingByAccuracy($partialEmailMatches, $meaningfulEmailPart, $dotlessMeaningfulEmailPart)
  {
    /**
     * @param $newPlayerEmail string email being a partial match
     * @return int the length of the partial match
     */
    $partialMatchLength = function($newPlayerEmail) use ($meaningfulEmailPart, $dotlessMeaningfulEmailPart) {
      return max(
        StringUtil::commonPrefixLength($newPlayerEmail, $meaningfulEmailPart),
        StringUtil::commonPrefixLength($newPlayerEmail, $dotlessMeaningfulEmailPart)
      );
    };

    usort($partialEmailMatches, function($plrA, $plrB) use ($partialMatchLength) {
      return $partialMatchLength($plrB->email) - $partialMatchLength($plrA->email);
    });

    $partialEmailMatches = array_slice($partialEmailMatches, 0, self::MAX_PARTIAL_EMAIL_MATCHES);
    return $partialEmailMatches;
  }
}
