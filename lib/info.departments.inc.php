<?php

$db = Db::get();
$stm = $db->query('SELECT id, name, description FROM `councils`');

$departments = array();
foreach ($stm->fetchAll() as $dep) {
  $id = $dep->id;
  $departments[$id] = $dep;
}

$smarty = new CantrSmarty();

$smarty->assign ("CONTACTLINK", "index.php?page=contact");
$smarty->assign ("FLYSPRAYLINK", "index.php?page=contact");
$smarty->assign ("RECRUITLINK", "https://forms.gle/FQRqW9o74YFWYtaT6");

if (($s > 0) && ($player > 0)) {
  $playerInfo = Request::getInstance()->getPlayer();
  $admin = $playerInfo->hasAccessTo(AccessConstants::ALTER_PRIVILEGES);
} else {
  $admin = 0;
}
$smarty->assign ("admin", $admin);
$smarty->assign ("s", $s);

function loadGabMembers(&$councils, $departments, Db $db) {
  $stm = $db->query("
    SELECT a.player, a.status, a.special, p.firstname, p.lastname, p.nick, p.forumnick, p.status AS pstatus, c.name AS council
    FROM assignments a 
    INNER JOIN players p ON a.player = p.id 
    LEFT JOIN councils c ON a.council = c.id 
    WHERE a.status = 1 
    ORDER BY p.id");

  foreach ($stm->fetchAll() as $member) {
    if ($member->council != $departments[1]->name) {
      $member->status = 3;
      $member->special = "(" . $member->council . ")";
    }
    $members []= clone $member;
  }

  $gabC = new StdClass;

  $gabC->id = 1;
  $gabC->name = $departments[1]->name;
  $gabC->Members = $members;

  $descr = $departments[1]->description;
  if( !empty($descr) ) $gabC->description = "<CANTR REPLACE NAME=$descr>";

  $councils []= $gabC;
}

function loadMembers( $dep, &$councils, $departments, Db $db) {
  $id = $dep->id;

  $stm = $db->prepare("
    SELECT a.player, a.status, a.special, p.firstname, p.lastname, p.nick, p.forumnick, p.status AS pstatus
    FROM assignments a INNER JOIN players p ON a.player = p.id
    WHERE council = :council ORDER BY a.status, a.special, p.lastname, p.firstname");
  $stm->bindInt("council", $id);
  $stm->execute();

    foreach ($stm->fetchAll() as $member) {
      if ($member->status > 0) {
        if ($member->special) {
          $member->special = ' (' . $member->special . ')';
        }
        $members [] = clone $member;
      }
    }

    $c = new StdClass;

    $c->id = $id;
    $c->name = $departments[$id]->name;
    $descr = $departments[$id]->description;
    if( !empty($descr) ) $c->description = "<CANTR REPLACE NAME=$descr>";
    $c->Members = $members;

    $councils []= $c;
}

$councils = array();

loadGabMembers($councils, $departments, $db);

  foreach ($departments as $dep) {
    if ($dep->id != 1) {
      loadMembers($dep, $councils, $departments, $db);
    }
  }

$smarty->assign ("Councils", $councils );
$smarty->displayLang ("info.departments.tpl", $lang_abr);
