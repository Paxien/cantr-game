<?php

class Timing
{
  /** @var Db */
  private $db;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  public function store($isApi)
  {
    global $sqlcount, $time, $sqltime, $page;
    $template = $isApi ? 3 : 1;

    $day = GameDate::NOW()->getDay();
    $stm = $this->db->prepare("UPDATE timing SET thecount = thecount + 1, sqlcount = sqlcount + :sqlCount,
      totaltime = totaltime + :time, sqltime = sqltime + :sqlTime WHERE pagetype = :page AND template = :template AND day = :day");
    $stm->bindInt("sqlCount", $sqlcount);
    $stm->bindFloat("time", $time);
    $stm->bindFloat("sqlTime", $sqltime);
    $stm->bindStr("page", $page);
    $stm->bindInt("template", $template);
    $stm->bindInt("day", $day);
    $stm->execute();

    if ($stm->rowCount() == 0) {
      $stm = $this->db->prepare("INSERT INTO timing (pagetype, thecount, sqlcount, totaltime, sqltime, template, day)
        VALUES (:page, 1, :sqlCount, :time, :sqlTime, :template, :day)");
      $stm->bindInt("sqlCount", $sqlcount);
      $stm->bindFloat("time", $time);
      $stm->bindFloat("sqlTime", $sqltime);
      $stm->bindStr("page", $page);
      $stm->bindInt("template", $template);
      $stm->bindInt("day", $day);
      $stm->execute();
    }
  }
}