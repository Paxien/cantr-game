<?php

class Report
{
  public static function saveInDb($rowName, $content, $emails = "", $rowTitle = "")
  {
    $db = Db::get();
    $gameDate = GameDate::NOW();
    $content = date("Y-m-d H:i:s") . " ({$gameDate->getDay()}-{$gameDate->getHour()}:{$gameDate->getMinute()}) $content";

    $stm = $db->prepare("SELECT 1 FROM reports WHERE name = :name");
    $stm->bindStr("name", $rowName);
    $alreadyExist = $stm->executeScalar();
    if (!$alreadyExist) {
      $stm = $db->prepare("INSERT INTO reports (name, contents, title, email) VALUES (:name, :contents, :title, :email)");
      $stm->bindStr("name", $rowName);
      $stm->bindStr("contents", $content);
      $stm->bindStr("title", $rowTitle);
      $stm->bindStr("email", $emails);
      $stm->execute();
    } else {
      $stm = $db->prepare("UPDATE reports SET contents = CONCAT(contents, :content) WHERE name = :name");
      $stm->bindStr("content", "\n\n" . $content);
      $stm->bindStr("name", $rowName);
      $stm->execute();
    }
  }

  public static function saveInPlayerReport($message)
  {
    $db = Db::get();
    $date = date("d/m H:i", time());
    $gameDate = GameDate::NOW();
    $turn = $gameDate->getDay() . "-" . $gameDate->getHour();

    $message = "$date (GMT) ($turn): " . $message;

    $stm = $db->prepare("INSERT INTO players_report (contents) VALUES (:contents)");
    $stm->bindStr("contents", $message);
    $stm->execute();
  }

  /**
   * @param $action string name of action for player or character
   * @param $id int ID of player or character
   */
  public static function saveInPcStatistics($action, $id) {
    $db = Db::get();
    $gameDate = GameDate::NOW();
    $stm = $db->prepare("INSERT INTO pcstatistics (action, turn, actiondate, id) VALUES (:action, :day, NOW(), :id)");
    $stm->bindStr("action", $action);
    $stm->bindInt("day", $gameDate->getDay());
    $stm->bindInt("id", $id);
    $stm->execute();
  }
}
