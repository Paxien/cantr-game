<?php

class LanguageGroupData
{
  var $name;
  var $newPlayersCount;
  var $newPlayersAfterOneLoginCount;
  var $newPlayersAfterMoreLoginCount;

  var $accountStillActive;
  var $accountRemovedByIdle;
  var $accountRemovedByUnsubscribe;

  var $lastPeriod_newPlayersCount;
  var $lastPeriod_newPlayersAfterOneLoginCount;
  var $lastPeriod_newPlayersAfterMoreLoginCount;
  var $lastPeriod_accountStillActive;
  var $lastPeriod_accountRemovedByIdle;
  var $lastPeriod_accountRemovedByUnsubscribe;

  public function __construct($_name)
  {
    $this->name = $_name;
    $this->newPlayersCount = 0;
    $this->newPlayersAfterOneLoginCount = 0;
    $this->newPlayersAfterMoreLoginCount = 0;
    $this->accountStillActive = 0;
    $this->accountRemovedByIdle = 0;
    $this->accountRemovedByUnsubscribe = 0;
    $this->lastPeriod_accountStillActive = 0;
    $this->lastPeriod_accountRemovedByIdle = 0;
    $this->lastPeriod_accountRemovedByUnsubscribe = 0;
    $this->lastPeriod_newPlayersAfterOneLoginCount = 0;
    $this->lastPeriod_newPlayersAfterMoreLoginCount = 0;
    $this->lastPeriod_newPlayersCount = 0;
  }

  public function printAsTableLine()
  {
    $style = "style=\"text-align:center; border-width: medium; border-style: outset; border-color:green\"";
    $lpstyle = "style=\"text-align:center; border-width: medium; border-style: outset; border-color:yellowgreen\"";
    return "\n\t<tr>" .
    "<td $style>$this->name</td>" .
    "<td $style>$this->newPlayersCount</td>" .
    "<td $style>$this->newPlayersAfterOneLoginCount</td>" .
    "<td $style>$this->newPlayersAfterMoreLoginCount</td>" .
    "<td $lpstyle>$this->lastPeriod_newPlayersAfterOneLoginCount</td>" .
    "<td $lpstyle>$this->lastPeriod_newPlayersAfterMoreLoginCount</td>" .
    "<td $style>$this->accountStillActive</td>" .
    "<td $style>$this->accountRemovedByIdle</td>" .
    "<td $style>$this->accountRemovedByUnsubscribe</td>" .
    "<td $lpstyle>$this->lastPeriod_accountStillActive</td>" .
    "<td $lpstyle>$this->lastPeriod_accountRemovedByIdle</td>" .
    "<td $lpstyle>$this->lastPeriod_accountRemovedByUnsubscribe</td>" .
    "</tr>";
  }

  public function isempty()
  {
    return $this->newPlayersCount == 0 && $this->newPlayersAfterOneLoginCount == 0 && $this->newPlayersAfterMoreLoginCount == 0 && $this->accountStillActive == 0
    && $this->accountRemovedByIdle == 0 && $this->accountRemovedByUnsubscribe == 0 && $this->lastPeriod_accountStillActive == 0
    && $this->lastPeriod_accountRemovedByIdle == 0 && $this->lastPeriod_accountRemovedByUnsubscribe == 0 && $this->lastPeriod_newPlayersAfterOneLoginCount == 0
    && $this->lastPeriod_newPlayersAfterOneLoginCount == 0;
  }
}

$page = "server.pr_report";
include "server.header.inc.php";

//preparing store data struct
$data = array();

function bindThisPeriod(DbStatement $stm) {
  $gameDate = GameDate::NOW();
  $stm->bindInt("from", $gameDate->getDay() - 30);
  $stm->bindInt("to", $gameDate->getDay());
}

function bindLastPeriod(DbStatement $stm) {
  $gameDate = GameDate::NOW();
  $stm->bindInt("from", $gameDate->getDay() - 60);
  $stm->bindInt("to", $gameDate->getDay() - 31);
}

$db = Db::get();
//prepare structures
$sum = new LanguageGroupData("&#931;");
$stm = $db->prepare("SELECT id, name FROM languages");
$stm->execute();
while (list($lId, $lName) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId] = new LanguageGroupData($lName);
}

///////////////get count of new players
$stm = $db->prepare("SELECT language, COUNT(*) FROM players
  WHERE players.register BETWEEN :from AND :to GROUP BY language");
bindThisPeriod($stm);
$stm->execute();
while (list($lId, $pCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->newPlayersCount = intval($pCount);
  $sum->newPlayersCount += intval($pCount);
}

///////////////get count of new players in LP
$stm = $db->prepare("SELECT language, COUNT(*) FROM players
  WHERE players.register BETWEEN :from AND :to GROUP BY language");
bindLastPeriod($stm);
$stm->execute();
while (list($lId, $pCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->lastPeriod_newPlayersCount = intval($pCount);
  $sum->lastPeriod_newPlayersCount += intval($pCount);
}

///////////////get still ACTIVE players, this period
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players
  LEFT JOIN languages ON players.language = languages.id 
  WHERE players.register BETWEEN :from AND :to AND status IN (:active, :approved) GROUP BY language");
bindThisPeriod($stm);
$stm->bindInt("active", PlayerConstants::ACTIVE);
$stm->bindInt("approved", PlayerConstants::APPROVED);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->accountStillActive = intval($aCount);
  $sum->accountStillActive += intval($aCount);
}
///////////////get still ACTIVE players, last period
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id
  WHERE players.register BETWEEN :from AND :to AND status IN (:active, :approved) GROUP BY language");
bindLastPeriod($stm);
$stm->bindInt("active", PlayerConstants::ACTIVE);
$stm->bindInt("approved", PlayerConstants::APPROVED);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->lastPeriod_accountStillActive = intval($aCount);
  $sum->lastPeriod_accountStillActive += intval($aCount);
}


/////////////How many new players of the this period leave Cantr because their was idle.
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players
  LEFT JOIN languages ON players.language = languages.id
  WHERE players.register BETWEEN :from AND :to AND status = :idledout GROUP BY language");
bindThisPeriod($stm);
$stm->bindInt("idledout", PlayerConstants::IDLEDOUT);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->accountRemovedByIdle = intval($aCount);
  $sum->accountRemovedByIdle += intval($aCount);
}

/////////////How many new players of the last period leave Cantr because their was idle.
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id
  WHERE players.register BETWEEN :from AND :to AND status = :idledout GROUP BY language");
bindLastPeriod($stm);
$stm->bindInt("idledout", PlayerConstants::IDLEDOUT);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->lastPeriod_accountRemovedByIdle = intval($aCount);
  $sum->lastPeriod_accountRemovedByIdle += intval($aCount);
}

/////////////How many new players of the this period leave Cantr because their was unsubscribe.
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id
  WHERE players.register BETWEEN :from AND :to AND status = :unsubscribed GROUP BY language");
bindThisPeriod($stm);
$stm->bindInt("unsubscribed", PlayerConstants::UNSUBSCRIBED);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->accountRemovedByUnsubscribe = intval($aCount);
  $sum->accountRemovedByUnsubscribe += intval($aCount);
}
/////////////How many new players of the last period leave Cantr because their was unsubscribe.
$stm = $db->prepare("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id
  WHERE players.register BETWEEN :from AND :to AND status = :unsubscribed GROUP BY language");
bindLastPeriod($stm);
$stm->bindInt("unsubscribed", PlayerConstants::UNSUBSCRIBED);
$stm->execute();
while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
  $data[$lId]->lastPeriod_accountRemovedByUnsubscribe = intval($aCount);
  $sum->lastPeriod_accountRemovedByUnsubscribe += intval($aCount);
}

/////////////////////////////////////Get count of players that they really login to game least once, two, or more. only registered in this period

//get login info
$stm = $db->prepare("SELECT id, SUM(ips.times) as logCount FROM players 
  LEFT JOIN ips ON players.id = ips.player
  WHERE players.register BETWEEN :from AND :to GROUP BY players.id ORDER BY logCount");
bindThisPeriod($stm);
$stm->execute();

$logedOnlyOnce = array();
$logedMore = array();
while (list($playerid, $logincount) = $stm->fetch(PDO::FETCH_NUM)) {
  if ($logincount == 1) {
    $logedOnlyOnce [] = $playerid;
  } else if ($logincount > 1) {
    $logedMore [] = $playerid;
  }
}

if (count($logedOnlyOnce) > 0) {
  $stm = $db->prepareWithIntList("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id WHERE players.id IN (:ids) GROUP BY language", [
    "ids" => $logedOnlyOnce,
  ]);
  $stm->execute();
  while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
    $data[$lId]->newPlayersAfterOneLoginCount = intval($aCount);
    $sum->newPlayersAfterOneLoginCount += intval($aCount);
  }
}
if (count($logedMore)) {
  $stm = $db->prepareWithIntList("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id WHERE players.id IN (:ids) GROUP BY language", [
    "ids" => $logedOnlyOnce,
  ]);
  $stm->execute();
  while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
    $data[$lId]->newPlayersAfterMoreLoginCount = intval($aCount);
    $sum->newPlayersAfterMoreLoginCount += intval($aCount);
  }
}

/////////////////////////////////////Get count of players that they really login to game least once, but only registered in last period

$stm = $db->prepare("SELECT id, SUM(ips.times) as logCount FROM players
  LEFT JOIN ips ON players.id = ips.player
  WHERE players.register BETWEEN :from AND :to GROUP BY players.id ORDER BY logCount");
bindLastPeriod($stm);
$stm->execute();
$logedOnlyOnce = array();
$logedMore = array();
while (list($playerid, $logincount) = $stm->fetch(PDO::FETCH_NUM)) {
  if ($logincount == 1) {
    $logedOnlyOnce [] = $playerid;
  } else if ($logincount > 1) {
    $logedMore [] = $playerid;
  }
}

if (count($logedOnlyOnce) > 0) {
  $stm = $db->prepareWithIntList("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id WHERE players.id IN (:ids) GROUP BY language", [
    "ids" => $logedOnlyOnce,
  ]);
  $stm->execute();
  while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
    $data[$lId]->lastPeriod_newPlayersAfterOneLoginCount = intval($aCount);
    $sum->lastPeriod_newPlayersAfterOneLoginCount += intval($aCount);
  }
}

if (count($logedMore) > 0) {
  $stm = $db->prepareWithIntList("SELECT languages.id, COUNT(*) FROM players 
  LEFT JOIN languages ON players.language = languages.id WHERE players.id IN (:ids) GROUP BY language", [
    "ids" => $logedMore,
  ]);
  $stm->execute();
  while (list($lId, $aCount) = $stm->fetch(PDO::FETCH_NUM)) {
    $data[$lId]->lastPeriod_newPlayersAfterMoreLoginCount = intval($aCount);
    $sum->lastPeriod_newPlayersAfterMoreLoginCount += intval($aCount);
  }
}


//////////////////////ignoring language group without any data
foreach ($data as $key => $entry) {
  if ($entry->isempty()) {
    unset($data[$key]);
  }
}
//adding summary data
$data[0] = $sum;

$message = <<<EOF
  <div style="width:100%;height:100%">
      <table style="border-style:solid;border-width:0px">
        <tr><th>Language</th><th>New Players Count(TP)</th><th>New Players Login Once(TP)</th><th>New Players Login More(TP)</th>
        <th>New Player Login Once(LP)</th><th>New Player Login More(LP)</th><th>New Player Still Active(TP)</th><th>Removed by Idle(TP)</th><th>Removed by Unsubscribe(TP)</th>
        <th>New Player Still Active(LP)</th><th>Removed by Idle(LP)</th><th>Removed by Unsubscribe(LP)</th>
        <tr>        
EOF;

foreach ($data as $key => $entry) {
  $message .= $entry->printAsTableLine();
}

$loginOncePercTP = round(100 * $sum->newPlayersAfterOneLoginCount / $sum->newPlayersCount, 1);
$loginMorePercTP = round(100 * $sum->newPlayersAfterMoreLoginCount / $sum->newPlayersCount, 1);
$loginOncePercLP = round(100 * $sum->lastPeriod_newPlayersAfterOneLoginCount / $sum->lastPeriod_newPlayersCount, 1);
$loginMorePercLP = round(100 * $sum->lastPeriod_newPlayersAfterMoreLoginCount / $sum->lastPeriod_newPlayersCount, 1);
$stillActivePercTP = round(100 * $sum->accountStillActive / $sum->newPlayersCount, 1);
$stillActivePercLP = round(100 * $sum->lastPeriod_accountStillActive / $sum->lastPeriod_newPlayersCount, 1);
$removeByIdlePercTP = round(100 * $sum->accountRemovedByIdle / $sum->newPlayersCount, 1);
$removeByIdlePercLP = round(100 * $sum->lastPeriod_accountRemovedByIdle / $sum->lastPeriod_newPlayersCount, 1);
$removeByUnsPercTP = round(100 * $sum->accountRemovedByUnsubscribe / $sum->newPlayersCount, 1);
$removeByUnsPercLP = round(100 * $sum->lastPeriod_accountRemovedByUnsubscribe / $sum->lastPeriod_newPlayersCount, 1);

$message .= "\n" . <<<EOF
      <tr><td style="border:0px;text-align:left" colspan="100"><p> * TP - players registered since this period, LP - players registered since last period</p></td></tr>
      </table>
<p style="margin-left:5px">
---------------------------------------------------------------------------<br />
---------------------------------------------------------------------------<br />
<table>
<tr>
  <td>New players login once (TP):&nbsp;&nbsp;&nbsp;</td> <td> $loginOncePercTP % ( where the 100% = New Players (TP) ) </td>
</tr>
<tr>
  <td>New players login more (TP): </td> <td> $loginMorePercTP % ( where the 100% = New Players (TP) ) </td>
</tr>
<tr>
  <td>New players login once (LP): </td> <td> $loginOncePercLP %  ( where the 100% = New Players (LP) )</td>
</tr>
<tr>
  <td>New players login more (LP): </td> <td> $loginMorePercLP %  ( where the 100% = New Players (LP) )</td>
</tr>
<tr>
  <td>New players still active (TP): </td> <td> $stillActivePercTP %  ( where the 100% = New Players (TP) )</td>
</tr>
<tr>
  <td>New players still active (LP): </td> <td> $stillActivePercLP % ( where the 100% = New Players (LP) )</td>
</tr>
<tr height="5px"/>
<tr>
  <td>Removed by idled (TP) : </td> <td> $removeByIdlePercTP % ( where the 100% = New Players (TP) )</td>
</tr>
<tr>
  <td>Removed by idled (LP) : </td> <td> $removeByIdlePercLP %  ( where the 100% = New Players (LP) )</td>
</tr>
<tr>
  <td>Removed by unsub (TP) : </td> <td> $removeByUnsPercTP %  ( where the 100% = New Players (TP) )</td>
</tr>
<tr>
  <td>Removed by unusb (LP) : </td> <td> $removeByUnsPercLP % ( where the 100% = New Players (LP) )</td>
</tr>
</table>
---------------------------------------------------------------------------<br />
---------------------------------------------------------------------------<br />
</p>
  </div>
EOF;

$logger = Logger::getLogger('server.pr_report.inc.php');
if ($_GET['printonly']) {
  echo $message;
} else {
  $turn = GameDate::NOW()->getDay();
  $smallerturn = $turn - 30;

  $mailService = new MailService("PR Department", $GLOBALS['emailMarketing']);
  if (!$mailService->send($GLOBALS['emailMarketing'], "PR - New Players report ($smallerturn - $turn)", $message, false)) {
    $emess = "<p>I can't send email, sorry. Problem: $php_errormsg</p>";
    $logger->error($emess);
  } else {
    $emess = "<p>Email have been sent.</p>";
    $logger->info($emess);
  }
}

include "server/server.footer.inc.php";
