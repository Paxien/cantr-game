<?php

class Limitations {

  const TYPE_KNOCK = 0;
  const TYPE_ATTACK_IMMUNITY = 1;
	const TYPE_LOCK_CHAR = 2;
	const TYPE_NOT_EAT_AFTER_VOMIT = 3;
  const TYPE_NOT_EAT_AFTER_NEAR_DEATH = 4;
  const TYPE_VIOLENCE_ATTACK_CHAR = 5;
  const TYPE_VIOLENCE_ATTACK_ANIMAL = 6;

  const TYPE_NEW_CHARACTERS = 7;
  const TYPE_PLAYER_CHARDESCRIPTION = 8;
  const TYPE_PLAYER_RADIO_USAGE = 9;
  const TYPE_PLAYER_NOTE_PREVIEW = 10;

  const TYPE_PLAYER_UNSUB_LOCK = 11;
  const TYPE_PLAYER_UNSUB_ALLOW = 12;

  const TYPE_OLD_AGE_DEATH_LOCK = 13;
  const TYPE_OLD_AGE_DEATH_ALLOW = 14;

  public static function getAllLims($type) {
    $type = intval($type);
    $ctime = self::getCtime();
    $db = Db::get();
    $stm = $db->prepare("SELECT char_id AS id, COUNT(*) AS count FROM char_limitations
      WHERE type = :type AND end_time >= :ctime GROUP BY char_id");
    $stm->bindInt("type", $type);
    $stm->bindInt("ctime", $ctime);
    $stm->execute();
    return $stm->fetchAll();
  }

  public static function getLims($char_id, $type, $target = null){
    $ctime = self::getCtime();

    // look for target specifics
    $target_query = ($target != null ? "AND target = " . intval($target) : "");

    $db = Db::get();
    $stm = $db->prepare("SELECT COUNT(*) AS count FROM char_limitations
      WHERE char_id = :charId AND type = :type $target_query AND end_time >= :ctime");
    $stm->bindInt("charId", $char_id);
    $stm->bindInt("type", $type);
    $stm->bindInt("ctime", $ctime);
    return $stm->executeScalar();
  }

  public static function addLim($char_id, $type, $rel_ctime, $target = null){
    if ($rel_ctime instanceof GameDate) {
      $rel_ctime = $rel_ctime->getTimestamp();
    }
    $end_time = self::getCtime() + $rel_ctime;
    $db = Db::get();
    $stm = $db->prepare("INSERT INTO char_limitations (char_id, type, end_time, target) VALUES (:charId, :type, :endTime, :target)");
    $stm->bindInt("charId", $char_id);
    $stm->bindInt("type", $type);
    $stm->bindInt("endTime", $end_time);
    $stm->bindInt("target", $target, true);
    $stm->execute();
  }

  public static function delLims($char_id, $type, $target = null){
    $target_query = ($target != null ? "AND target = " . intval($target) : "");
    $db = Db::get();
    $stm = $db->prepare("DELETE FROM char_limitations WHERE char_id = :charId AND type = :type $target_query");
    $stm->bindInt("charId", $char_id);
    $stm->bindInt("type", $type);
    $stm->execute();
  }

  public static function getTimeLeft($char_id, $type, $target = null){
    $target_query = ($target != null ? "AND target = " . intval($target) : "");
    $db = Db::get();
    $stm = $db->prepare("SELECT end_time FROM char_limitations WHERE char_id = :charId
      AND type = :type $target_query AND end_time >= :endTime ORDER BY end_time ASC LIMIT 1");
    $stm->bindInt("charId", $char_id);
    $stm->bindInt("type", $type);
    $stm->bindInt("endTime", self::getCtime());
    $timeleft = $stm->executeScalar();
    if ($timeleft !== null) {
      return $timeleft - self::getCtime();
    }
    else return 0; // if there is no limit
  }

  public static function getCtime(){
    $turn = GameDate::NOW();
    return $turn->getSecond() + $turn->getMinute() * 60 + $turn->getHour() * 60 * 36 + $turn->getDay() * 60 * 36 * 8;
  }

  // cantr time to day hour minute second
  public static function ctodhms($ctime){
    $time = array();
    $time['second'] = $ctime%60;
    $ctime = floor($ctime/60);
    $time['minute'] = $ctime%36;
    $ctime = floor($ctime/36);
    $time['hour'] = $ctime%8;
    $ctime = floor($ctime/8);
    $time['day'] = $ctime;
    return $time;
  }

  public static function dhmstoc($day, $hour, $minute, $second){
    return $second + $minute*60 + $hour*60*36 + $day*60*36*8;
  }
}
