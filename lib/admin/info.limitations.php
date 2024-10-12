<?php

function getAllLimitationsByType($type) {
  $lims = Limitations::getAllLims($type);
  $limsView = array();
  foreach ($lims as $lim) {
    $timeLeft = GameDate::fromTimestamp(Limitations::getTimeLeft($lim->id, $type));
    $limsView[] = array(
      "id" => $lim->id,
      "count" => $lim->count,
      "time" => $timeLeft->getArray(),
      "lift" => GameDate::NOW()->plus($timeLeft)->getArray(),
    );
  }
  return $limsView;
}

function getPlayerName($id) {
  $name = Player::loadById($id)->getFullName();
  return "<a href=\"index.php?page=infoplayer&player_id={$id}\">". $name . "</a>";
}

function getCharName($id) {
  $char = Character::loadById($id);
  $name = $char->getName();
  if ($char->getStatus() > CharacterConstants::CHAR_ACTIVE) {
    $name = '<span style="color:#888;">'. $name . "</span>";
  }
  return $name ." of {$char->getPlayer()} [". getPlayerName($char->getPlayer()) ."]";
}

$admin = Request::getInstance()->getPlayer();

if ($admin->hasAccessTo(AccessConstants::VIEW_PLAYERS)) {

  $smarty = new CantrSmarty();
  
  $plrLimTypes = array(
    Limitations::TYPE_NEW_CHARACTERS => "creating new chars",
    Limitations::TYPE_PLAYER_CHARDESCRIPTION => "changing character descriptions",
    Limitations::TYPE_PLAYER_RADIO_USAGE => "using radio"
  );

  $allLimitations = array();
  $limitations = array();
  foreach ($plrLimTypes as $limType => $limTypeName) {
    $lims = getAllLimitationsByType($limType);
    foreach ($lims as &$lim) {
      $lim["name"] = getPlayerName($lim["id"]);
    }
    $limitations[$limTypeName] = $lims;
  }
  $allLimitations['player'] = $limitations;

  $charLimTypes = array(Limitations::TYPE_LOCK_CHAR => "lock access to character");
  $limitations = array();
  foreach ($charLimTypes as $limType => $limTypeName) {
    $lims = getAllLimitationsByType($limType);
    foreach ($lims as &$lim) {
      $lim['name'] = getCharName($lim["id"]);
    }
    $limitations[$limTypeName] = $lims;
  }
  
  $allLimitations['character'] = $limitations;
  $smarty->assign("limitations", $allLimitations);

  $db = Db::get();
  $stm = $db->prepareWithIntList("SELECT p.id, MAX(p.lastdate), COUNT(c.id) as aliveChars FROM players p
      LEFT JOIN chars c ON c.player = p.id AND c.status IN (:statuses)
    WHERE p.status = :locked GROUP BY p.id ORDER BY MAX(p.lastdate) DESC", [
    "statuses" => [CharacterConstants::CHAR_PENDING, CharacterConstants::CHAR_ACTIVE],
  ]);
  $stm->bindInt("locked", PlayerConstants::LOCKED);
  $stm->execute();

  $lockedPlrs = $stm->fetchAll();
  foreach ($lockedPlrs as $plr) {
    $plr->name = getPlayerName($plr->id);
  }

  $smarty->assign("lockedPlrs", $lockedPlrs);
  
  $smarty->displayLang("admin/info.limitations.tpl", $lang_abr);
  
} else {
  CError::throwRedirect("player", "You are not authorized to see this page.");
}
