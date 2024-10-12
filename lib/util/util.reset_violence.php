<?php

$db = Db::get();

$stm = $db->prepare("DELETE FROM char_limitations WHERE type = :type");
$stm->bindInt("type", Limitations::TYPE_VIOLENCE_ATTACK_CHAR);
$stm->execute();

$db->query("DELETE FROM hunting");
