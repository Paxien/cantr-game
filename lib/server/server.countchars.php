<?php

// File: server.countchars.php
//
// Purpose: can be run by crontab to count characters, so that using counts of characters in imagetest
//          (or in the future administrative map function) does not take too much capacity, and so that
//          it can be used potentially for a population-density-dependent spawning system.
//
// WARNING: if there is a spawning function dependent on this table, and there is an applicant right at
//          the time this process is running, it is a bit nasty :) (the spawning function actually has
//          a protection for this already built int)

$page = "server.countchars";
include "server.header.inc.php";

$db = Db::get();
// Empty the counters first
$db->query("DELETE FROM char_on_loc_count");

$stm = $db->prepare("
  SELECT
    ch.language,
    ch.location,
    COUNT(*) AS num,
    l.type,
    l.region
  FROM
    chars ch
    LEFT JOIN settings_chars ch_settings ON ch_settings.person = ch.id
      AND ch_settings.type = :settingType
      AND ch_settings.data = 1
    INNER JOIN locations l ON ch.location = l.id 
  WHERE location > 0 AND status = :active
    AND ch_settings.id IS NULL
  GROUP BY ch.language, ch.location
  ORDER BY location, language");
$stm->bindInt("settingType", CharacterSettings::OPT_OUT_FROM_SPAWNING_SYSTEM);
$stm->bindInt("active", CharacterConstants::CHAR_ACTIVE);
$stm->execute();

foreach ($stm->fetchAll() as $X) {
  $infos[$X->location][$X->language] = clone $X;
}

$result = array();

foreach ($infos as $location => $languages) {
  foreach ($languages as $language => $info) {
    $root = $location;
    if ($info->type == 1) {
      // A town location. put it in the resulting array directly
      $result[$location][$language] = $info;
      $result[$location][$language]->root = $root;
    }
    else if ($info->region != 0) {
      // idoors or in a non-moving vehicle. Try to find the town location
      $root = $info->region;
      while ($infos[$root][$language]->type > 1
          && $infos[$root][$language]->region > 0) {
        $root = $infos[$root][$language]->region;
      }

      // If we found a town location, increase its count
      if ($infos[$root][$language]->type == 1) {
        if (array_key_exists($root, $result) && array_key_exists($language, $result[$root])) {
          $result[$root][$language]->num += $info->num;
        } else {
          $result[$root][$language] = $info;
        }
        $result[$root][$language]->root = $root;
      }
    }
  }
}

foreach ($result as $location => $languages) {
  foreach ($languages as $language => $info) {
    $stm = $db->prepare("
      INSERT INTO char_on_loc_count (location, root, language, number, sq_number)
      VALUES (:locationId, :root, :language, :number, :sqNumber)");
    $stm->bindInt("locationId", $info->location);
    $stm->bindInt("root", $info->root);
    $stm->bindInt("language", $info->language);
    $stm->bindInt("number", $info->num);
    $stm->bindInt("sqNumber", $info->num * $info->num);
    $stm->execute();
  }
}

include "server/server.footer.inc.php";
