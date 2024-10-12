<?php

function expire_object ($object_id) {
  $db = Db::get();
  $object_id = intval($object_id);
  if ($object_id > 0) {

    $stm = $db->prepare("UPDATE objects SET person = person * -1, location = location * -1, attached = attached * -1,
                   expired_date = :date WHERE id = :objectId AND expired_date = 0 LIMIT 1");
    $stm->bindInt("date", GameDate::NOW()->getIntInDbFormat());
    $stm->bindInt("objectId", $object_id);
    $stm->execute();
    return ($stm->rowCount() > 0);
  }
  return false;
}

function expire_multiple_objects ($criteria) { // TODO it shouldn't glue the query
  $db = Db::get();
  $stm = $db->prepare("UPDATE objects SET person = person * -1, location = location * -1, attached = attached* -1,
    expired_date = :date WHERE $criteria AND expired_date = 0");
  $stm->bindInt("date", GameDate::NOW()->getIntInDbFormat());
  $stm->execute();
}

function purge_expired_objects(GameDate $gameDate, Db $db)
{
  $purgedate = $gameDate->getIntInDbFormat() - (_EXPFTHBCK + 1) * 10;
  $k = 0;
  do {
    $stm = $db->prepare("DELETE FROM objects WHERE expired_date > 0 and expired_date < :date LIMIT :limit");
    $stm->bindInt("date", $purgedate);
    $stm->bindInt("limit", 2000);
    $stm->execute();
    $k++;
  } while ($k < 10 && $stm->rowCount() > 0);
}

function usage_decay_object($object_id,$factor=1) {
  $db = Db::get();
  $stm = $db->prepare("SELECT t.deter_rate_use, o.deterioration FROM objecttypes t, objects o WHERE o.type = t.id AND o.id = :objectId");
  $stm->bindInt("objectId", $object_id);
  $stm->execute();
  $decay = $stm->fetchObject();

  $decay->deter_rate_use = $decay->deter_rate_use * $factor;

  if ($decay->deterioration + $decay->deter_rate_use < 10000 ) {
    $stm = $db->prepare("UPDATE objects SET deterioration = deterioration + :deterRate WHERE id = :objectId");
    $stm->bindFloat("deterRate", $decay->deter_rate_use);
    $stm->bindInt("objectId", $object_id);
    $stm->execute();
  } else {
    $stm = $db->prepare("UPDATE objects SET deterioration = 10001 WHERE id = :objectId");
    $stm->bindInt("objectId", $object_id);
    $stm->execute();

    notify_expiration($object_id);
    expire_object($object_id);
  }
}

function notify_expiration($object_id)
{
  try {
    $object = CObject::loadById($object_id);

  if ($object->getPerson() > 0) {
    $char = Character::loadById($object->getPerson());
    Event::create(126, "OBJECT=" . $object->getId())
      ->forCharacter($char)->show();
  } elseif ($object->getLocation() > 0) {
    Event::create(126, "OBJECT=" . $object->getId())
      ->inLocation($object->getLocation())->show();
  }
  } catch (InvalidArgumentException $e) {
    Logger::getLogger(__FILE__)->warn("Unable to notify about expiration of object $object_id", $e);
  }
}
