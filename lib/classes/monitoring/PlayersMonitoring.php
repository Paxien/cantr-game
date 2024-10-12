<?php

class PlayersMonitoring
{
  public static function reportObjectTranslocation(CObject $object, Character $from, Character $to, $amount, Db $db)
  {
    $stm = $db->prepareWithIntList("SELECT player, IF (id = :fromId, 1, 0) AS isgiver FROM chars
      WHERE id IN (:ids) GROUP BY player ORDER BY isgiver DESC", [
        "ids" => [$from->getId(), $to->getId()],
    ]);
    $stm->bindInt("fromId", $from->getId());
    $stm->execute();
    $sameplayer = $stm->rowCount() < 2;
    if (!$sameplayer) {
      $plyrA = $stm->fetchColumn();
      $plyrB = $stm->fetchColumn();
      $stm = $db->prepare("SELECT * FROM troubleplayers WHERE ids LIKE :id1 AND ids LIKE :id2");
      $stm->bindStr("id1", "%|$plyrA|%");
      $stm->bindStr("id2", "%|$plyrB|%");
      $stm->execute();
      $multi = $stm->fetchObject();
      if ($multi) {
        $ids = explode("|", $multi->ids);
        $cc = 0;
        foreach ($ids as $id) {
          if ($id == $plyrA) {
            $colourA = $cc;
          } elseif ($id == $plyrB) {
            $colourB = $cc;
          }
          $cc++;
        }
      }
    }

    if ($sameplayer || $multi) {
      $gameDate = GameDate::NOW();
      $turnInfo = $gameDate->getObject();

      $stm = $db->prepare("SELECT p.id, p.firstname, p.lastname, ch1.name AS char1, ch2.name AS char2
        FROM players p, chars ch1, chars ch2
        WHERE ch1.id = :fromId AND ch2.id = :toId AND p.id = ch1.player");
      $stm->bindInt("fromId", $from->getId());
      $stm->bindInt("toId", $to->getId());
      $stm->execute();
      $coopinfo = $stm->fetchObject();
      if ($sameplayer) {
        $coopinfostr = "$coopinfo->id|$coopinfo->firstname $coopinfo->lastname|";
      } else {
        $coopinfostr = "+$multi->id||";
      }
      $coopinfostr .= "$coopinfo->char1|$coopinfo->char2|{$turnInfo->day}-{$turnInfo->hour}|{$turnInfo->minute}";

      if ($object->getSetting() == ObjectConstants::SETTING_QUANTITY) {
        if ($object->getType() == ObjectConstants::TYPE_RAW) {
          $stm = $db->prepare("SELECT rt.name FROM rawtypes rt
            INNER JOIN objects o ON rt.id = o.typeid WHERE o.id = :objectId");
          $stm->bindInt("objectId", $object->getId());
          $objName = $stm->executeScalar();
          $coopinfostr .= "|{$amount}g of $objName";
        } else { // coins
          $coinName = $object->getSpecifics();
          $coopinfostr .= "|{$amount} pieces of " . $object->getName() . " '$coinName'";
        }
      } else {
        $stm = $db->prepare("SELECT unique_name FROM objecttypes ot
          INNER JOIN objects o ON ot.id = o.type WHERE o.id = :objectId");
        $stm->bindInt("objectId", $object->getId());
        $objName = $stm->executeScalar();
        $coopinfostr .= "|$objName";
      }

      if ($multi) {
        $coopinfostr .= "|$colourA|$colourB";
      }

      Report::saveInDb("goodspassing", $coopinfostr);
    }
  }

  /**
   * @param Dragging $dragging
   * @param Character|CObject $victim
   * @param Location $fromLoc
   * @param Location $toLoc
   * @param Db $db
   */
  public static function reportDragging(Dragging $dragging, $victim, Location $fromLoc, Location $toLoc, Db $db)
  {

    $isVictimHuman = ($dragging->getVictimType() == DraggingConstants::TYPE_HUMAN);

    if (!$isVictimHuman) { // some of object dragging don't need to be reported. Except: dead bodies, animals, multipeople dragging
      $draggingDeadBody = $victim->getType() == ObjectConstants::TYPE_DEAD_BODY;
      $draggingDomesticatedAnimal = $victim->getObjectCategory()->getId() == ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS;
      $moreThanOneDragger = count($dragging->getDraggers()) > 1;
      if (!($draggingDeadBody || $draggingDomesticatedAnimal || $moreThanOneDragger)) {
        return; // not need to report that
      }
    }

    $fromLocName = $fromLoc->getName();
    $toLocName = $toLoc->getName();

    if ($fromLoc->getType() == LocationConstants::TYPE_OUTSIDE) {
      $stm = $db->prepare("SELECT name, usersname FROM oldlocnames WHERE id = :id");
      $stm->bindInt("id", $fromLoc->getId());
      $stm->execute();
      list($oldName, $usersName) = $stm->fetch(PDO::FETCH_NUM);
        $fromLocName = "$oldName [$usersName]";
    }
    if ($toLoc->getType() == LocationConstants::TYPE_OUTSIDE) {
      $stm = $db->prepare("SELECT name FROM oldlocnames WHERE id = :id");
      $stm->bindInt("id", $fromLoc->getId());
      $stm->execute();
      list($oldName, $usersName) = $stm->fetch(PDO::FETCH_NUM);
        $toLocName = "$oldName [$usersName]";
    }

    $draggers = $dragging->getDraggers();
    $stm = $db->prepareWithIntList("SELECT c.id, c.name, c.player AS plrid, CONCAT(p.firstname, ' ', p.lastname) AS plrname FROM chars c
      INNER JOIN players p ON p.id = c.player WHERE c.id IN (:draggers)", [
      "draggers" => $draggers,
    ]);
    $stm->execute();
    $draggersList = array();
    foreach ( $stm->fetchAll() as $draggerInfo) {
      $draggersList[] = "$draggerInfo->name (id: $draggerInfo->id of $draggerInfo->plrname [id: $draggerInfo->plrid])";
    }
    $allDraggersString = implode(", ", $draggersList);

    if ($isVictimHuman) {
      $stm = $db->prepare("SELECT id, CONCAT(firstname, ' ', lastname) AS plrname FROM players WHERE id = :playerId");
      $stm->bindInt("playerId", $victim->getPlayer());
      $stm->execute();
      $playerInfo = $stm->fetchObject();
      $victimName = $victim->getName();
      $message = "$victimName (" . $victim->getId() . ") of $playerInfo->plrname ($playerInfo->id) ";
    } else {
      if ($victim->getType() == ObjectConstants::TYPE_RAW) {
        $objectName = ObjectHandler::getRawNameFromId($victim->getTypeid());
      } else {
        $objectName = $victim->getName();
      }
      $message = "$objectName (oid: " . $victim->getId() . ", type: " . $victim->getType() . ") ";
    }

    $message .= " dragged from $fromLocName (id: " . $fromLoc->getId() . ") " .
      "to $toLocName (id: " . $toLoc->getId() . ") by $allDraggersString.\n\n";

    Report::saveInDb("dragging", $message);
  }
}
