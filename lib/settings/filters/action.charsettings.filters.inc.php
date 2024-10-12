<?php

$names = HTTPContext::getRawString('names');
$codedData = HTTPContext::getRawString('codedData');

if (!isset($names) || !isset($codedData)) {
   CError::throwRedirect("char.events", "Don't try this..");
}

$names = json_decode( $names, true );
$codedData = json_decode( $codedData, true );

$db = Db::get();

//removing old filters
$stm = $db->prepare( "DELETE FROM settings_chars WHERE person = :charId AND type = :type");
$stm->bindInt("charId", $char->getId());
$stm->bindInt("type", CharacterSettings::EVENT_FILTER);
$stm->execute();

foreach ($codedData as $key => $filter) {
  $filterName = $names[$key];
  if( !$filterName )continue;

  $filterDataField = "$filterName|" . implode(',', $filter );
  $stm = $db->prepare("INSERT INTO settings_chars(type, person, data) VALUES(:type, :charId, :data)");
  $stm->bindInt("type", CharacterSettings::EVENT_FILTER);
  $stm->bindInt("charId", $char->getId());
  $stm->bindStr("data", $filterDataField);
  $stm->execute();
}
