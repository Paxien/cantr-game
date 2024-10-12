<?php

class MessageManager
{
  /** @var Db */
  private $db;

  const PQUEUE_SYSTEM_MESSAGE = 0;
  const PQUEUE_PD_NOTIFICATION = 2;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  public function sendMessage($sender, $receiver, $message, $new)
  {
    $turn = GameDate::NOW()->formatDayWithHourAndMinute();
    $date = date("d/m H:i", time());
    $message = "<div class=\"message-reply-to\"><span class=\"message-date\">$date (GMT) ($turn)</span> " . $message . "</div>";
    $stm = $this->db->prepare("INSERT INTO pqueue (`from`, `player`, `content`, `new_default`, `new`)
      VALUES (:sender, :receiver, :message, :new1, :new2)");
    $stm->bindInt("sender", $sender);
    $stm->bindInt("receiver", $receiver);
    $stm->bindStr("message", $message);
    $stm->bindInt("new1", $new);
    $stm->bindInt("new2", $new);
    $stm->execute();
  }
}