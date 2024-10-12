<?php

class PlayerDailyReport
{
  /** @var int */
  private $day;
  /** @var Player */
  private $player;

  /** @var Logger */
  private $logger;
  /** @var Db */
  private $db;

  public function __construct(Player $player, $day)
  {
    $this->player = $player;
    $this->day = $day;
    $this->logger = Logger::getLogger(__CLASS__);
    $this->db = Db::get();
  }

  public function sendMail()
  {
    $player = $this->player->getId();

    $stm = $this->db->prepare("SELECT COUNT(player) AS number FROM unreported_turns
      WHERE player = :playerId AND turnnumber = :number");
    $stm->bindInt("playerId", $this->player->getId());
    $stm->bindInt("number", $this->day);
    $number = $stm->executeScalar();

    if ($number == 0) {
      throw new IllegalStateException("email for day $this->day was already sent.");
    }

    // END OF VALIDATION

    $message = "Cantr II Turn Report for " . $this->player->getFullName() . " (ID $player)\n\n";
    $message .= "Please do read the text below if you did not do so already.\n\n";

    $stm = $this->db->prepare("SELECT m.id, m.date, m.author,
        (SELECT m2.content FROM messages m2 WHERE m2.id = m.id AND m2.language IN (:language, 1)
        ORDER BY m2.language DESC LIMIT 1) AS content
      FROM messages m
      WHERE m.language = 1 GROUP BY m.id, m.date, m.author ORDER BY m.id ");
    $stm->bindInt("language", $this->player->getLanguage());
    $stm->execute();
    foreach ($stm->fetchAll() as $message_info) {
      $message .= "  $message_info->content ($message_info->date)\n";
    }

    $stm = $this->db->prepare("SELECT * FROM pqueue WHERE player = :playerId ORDER BY id");
    $stm->bindInt("playerId", $this->player->getId());
    $stm->execute();
    foreach ($stm->fetchAll() as $event_info) {
      $message .= "  $event_info->content\n";
    }

    $stm = $this->db->prepare("SELECT id FROM chars WHERE player = :playerId AND status = :active ORDER BY name");
    $stm->bindInt("playerId", $this->player->getId());
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $stm->execute();
    foreach ($stm->fetchScalars() as $charId) {

      try {
        $char = Character::loadById($charId);

        $tag = new tag;
        $tag->character = $char->getId();
        $tag->language = $char->getLanguage();
        $tag->html = false;
        $tag->content = "<CANTR LOCNAME ID=" . $char->getLocation() . ">";
        $tag->content = $tag->interpret();
        $locName = $tag->interpret();

        $message .= "\nEvents for " . $char->getName() . " ($locName):\n\n";

        // oldest event ID from this day
        $stm = $this->db->prepare("SELECT MIN(e.id), MAX(e.id) FROM events e INNER JOIN events_obs eo ON eo.event = e.id
          WHERE eo.observer = :observer AND e.day = :day");
        $stm->bindInt("observer", $char->getId());
        $stm->bindInt("day", $this->day);
        $stm->execute();
        list($lastEvent, $newestEvent) = $stm->fetch(PDO::FETCH_NUM);

        $lastEvent = max($lastEvent - 1, 0); // eventList's lastEvent is exclusive, so we need to include it somehow
        $newestEvent = max($newestEvent, 0);

        $eventList = new EventListView($char, false);
        $events = $eventList->interpret($lastEvent, $newestEvent, false);

        foreach ($events as &$event) {
          $event = "  " . $event;
        }

        $message .= implode("\n", $events) . "\n";
      } catch (InvalidArgumentException $e) {
        $this->logger->error("Cannot load character $charId for player " . $this->player->getId(), $e);
      }
    }

    $stm = $this->db->prepare("SELECT COUNT(id) FROM chars WHERE status = :active");
    $stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
    $charsCount = $stm->executeScalar();

    $stm = $this->db->prepare("SELECT COUNT(id) FROM players WHERE status < :locked");
    $stm->bindInt("locked", PlayerConstants::LOCKED);
    $playersCount = $stm->executeScalar();

    $tag = new tag;
    $tag->language = $this->player->getLanguage();
    $tag->content = "<CANTR REPLACE NAME=mail_turn_report_footer PLAYERS=$playersCount CHARS=$charsCount>";
    $tag->html = false;
    $bottommessage = $tag->interpret();

    $message .= $bottommessage;

    $htmlMail = PlayerSettings::getInstance($player)->get(PlayerSettings::HTML_MAIL);

    $mailService = new MailService("Cantr Players Department", $GLOBALS['emailSupport']);
    $mailTitle = "Cantr Turn Report " . $this->day . " for " . $this->player->getFullName();

    if ($htmlMail) {
      $message = nl2br($message);
      $mailSent = $mailService->send($this->player->getEmail(), $mailTitle, $message);
    } else {
      $mailSent = $mailService->sendPlaintext($this->player->getEmail(), $mailTitle, $message);
    }

    if ($mailSent) {
      $stm = $this->db->prepare("DELETE FROM unreported_turns WHERE player = :playerId AND turnnumber = :number");
      $stm->bindInt("playerId", $this->player->getId());
      $stm->bindInt("number", $this->day);
      $stm->execute();

      $stm = $this->db->prepare("DELETE FROM `pqueue` WHERE `player` = :playerId AND `from` = 0"); // remove general reports about characters
      $stm->bindInt("playerId", $player);
      $stm->execute();
    } else {
      $this->logger->warn("Errors when sending turn report for player " . $this->player->getId());
    }
  }

}