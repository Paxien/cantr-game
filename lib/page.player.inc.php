<?php

include_once "func.genes.inc.php";

//this is only one page, when this same tag can produce diffrent output,
//because we can run tag for diffrent characters.
define("IGNORETAGCACHE", 1);

$playerInfo = Request::getInstance()->getPlayer();

$db = Db::get();

// Smarty init
$smarty = new CantrSmarty;

$tag = new tag;
$tag->language = $l;

$smarty->assign("playerID", $playerInfo->getId());
$smarty->assign("name", $playerInfo->getFullName());
$smarty->assign("username", $playerInfo->getUserName());
$smarty->assign("email", $playerInfo->getEmail());
$smarty->assign("status", "<CANTR REPLACE NAME=status_" .
  ($playerInfo->isOnLeave() ? "onleave" : "active") . ">");

// did you know that... - curiosities
if (PlayerSettings::getInstance($player)->get(PlayerSettings::DID_YOU_KNOW) == 0) {
  $stm = $db->prepare("SELECT COUNT(*) FROM texts WHERE name LIKE 'curiosity_%' AND language = 1");
  $curiosities = $stm->executeScalar();

  $num = mt_rand(0, $curiosities - 1);
  $stm = $db->prepare("SELECT name FROM texts WHERE name LIKE 'curiosity_%' AND language = 1 LIMIT :offset, 1");
  $stm->bindInt("offset", $num);
  $curioName = $stm->executeScalar();
  $smarty->assign("curiosity", $curioName);
}

if (Limitations::getLims($player, Limitations::TYPE_PLAYER_UNSUB_LOCK) > 0) {
  $unsubTimeLeft = Limitations::getTimeLeft($player, Limitations::TYPE_PLAYER_UNSUB_LOCK);
  $smarty->assign("unsubDate", GameDate::NOW()->plus(GameDate::fromTimestamp($unsubTimeLeft))->getObject());
}

// surveys
$survey = new Survey($db);
$survList = $survey->listOfAvailableSurveys($playerInfo->getId(), $l);

$isPlayerOldEnough = $playerInfo->getAgeInDays() > PlayerConstants::SURVEY_MIN_AGE;
if ($isPlayerOldEnough && count($survList) > 0) {
  ob_start();
  foreach ($survList as $survID) {
    $survey->loadSurvey($survID);
    $surveySmarty = new CantrSmarty();
    $surveySmarty->assign("survey", $survey->showSurvey());
    $surveySmarty->assign("show_back", false);

    $surveySmarty->displayLang("page.show_survey.tpl", $lang_abr);
  }

  $survs = ob_get_clean();
  $smarty->assign("survs", $survs);
}

// Global messages

$stm = $db->prepare("SELECT m.id, m.date, m.author,
  (SELECT m2.content FROM messages m2
    WHERE m2.id = m.id AND m2.language IN (" . $playerInfo->getLanguage() . ", 1)
    ORDER BY m2.language DESC LIMIT 1) AS content,
  ms.message AS seen FROM messages m
  LEFT JOIN message_seen ms ON ms.player = :playerId AND ms.message = m.id
  WHERE m.language = 1 GROUP BY m.id, m.date, m.author, content, seen");
$stm->bindInt("playerId", $player);
$stm->execute();
$messages = [];
foreach ($stm->fetchAll() as $message_info) {
  if (!$message_info->seen) {
    $messages[] = $message_info;
  }
}

$smarty->assign("messages", $messages);

// Individual messages

$stm = $db->prepare("SELECT * FROM pqueue WHERE player = :playerId");
$stm->bindInt("playerId", $playerInfo->getId());
$stm->execute();
$imessages = [];
foreach ($stm->fetchAll() as $queue_info) {
  if ($queue_info->from != 0) {
    $queue_info->reply = strpos($queue_info->content, "QQQadd reply buttonQQQ") !== false;
  }
  $queue_info->content = preg_replace("/QQQadd reply buttonQQQ/", "", $queue_info->content);
  $imessages[] = $queue_info;
}

$smarty->assign("imessages", $imessages);

$stm = $db->prepare("UPDATE pqueue SET new = new - 1 WHERE player = :playerId AND new > 0");
$stm->bindInt("playerId", $playerInfo->getId());
$stm->execute();

// Notify sender
$stm = $db->prepare("
  SELECT pqueue.*
  FROM pqueue, assignments
  WHERE pqueue.player = :playerId AND pqueue.new + 1 = pqueue.new_default
    AND (assignments.council = 6 OR assignments.council = 8)
    AND pqueue.from=assignments.player
  GROUP BY pqueue.id
");
$stm->bindInt("playerId", $playerInfo->getId());
$stm->execute();

$messageManager = new MessageManager($db);
foreach ($stm->fetchAll() as $queue_info) {
  $message = "<b>Your message to " . $playerInfo->getFullName() . " (" . $playerInfo->getId() . ") " .
    "has been displayed for the first time:</b> <br><br>" . $queue_info->content;
  $messageManager->sendMessage(MessageManager::PQUEUE_PD_NOTIFICATION, $queue_info->from, $message, 0);
}

$request = Request::getInstance();
$charactersListView = new CharactersListView($request->getPlayer(), $db);
$smarty->assign("charactersList", $charactersListView->show());

$globalConfig = new GlobalConfig($db);
$canCreateCharacters = in_array($playerInfo->getStatus(), [
    PlayerConstants::APPROVED,
    PlayerConstants::ACTIVE,
  ]);
$smarty->assign("canCreateCharacters", $canCreateCharacters);

if ($env->introExists()) {
  $introDb = Db::get("intro");
  $stm = $introDb->prepareWithIntList("SELECT id FROM players WHERE id = :player AND status IN (:statuses)",
    ["statuses" => [
      PlayerConstants::PENDING,
      PlayerConstants::APPROVED,
      PlayerConstants::ACTIVE,
    ]]);
  $stm->bindInt("player", $player);
  $stm->execute();
  if ($stm->exists()) {
    $introCharactersListView = new IntroCharactersListView($request->getPlayer(),
      $introDb);
    $introCharactersListView->setDisplayInfoOnEmpty(false);
    $smarty->assign("introCharactersList", $introCharactersListView->show());
  }
}

//Voting links
$stm = $db->prepare("SELECT url FROM votinglinks WHERE language IN (0, :language) AND enabled=1 ORDER BY `order`");
$stm->bindInt("language", $l);
$stm->execute();
$votingLinks = [];
foreach ($stm->fetchAll() as $vlink_info) {
  $votingLinks[] = htmlspecialchars_decode($vlink_info->url);
}

$smarty->assign("votingLinks", $votingLinks);

//Turn reports
$stm = $db->prepare("SELECT turnnumber FROM unreported_turns WHERE player = :playerId ORDER BY turnnumber");
$stm->bindInt("playerId", $playerInfo->getId());
$stm->execute();
$turnreps = $stm->fetchScalars();

$smarty->assign("turnreps", $turnreps);

$accessList = $playerInfo->getAccessList();
if (in_array(AccessConstants::ACCEPT_PLAYERS, $accessList)) {
  $stm = $db->prepare("SELECT COUNT(*) FROM newplayers");
  $newPlayersCount = $stm->executeScalar();
  $smarty->assign("newplayerscount", $newPlayersCount);
}

//PayPal
$stm = $db->prepare("SELECT paypal_lc FROM languages WHERE id = :language LIMIT 1");
$stm->bindInt("language", $playerInfo->getLanguage());
$lc = $stm->executeScalar();

$accessAssocArray = [];
if (count($accessList) > 0) {
  $accessAssocArray = array_combine($accessList, array_fill(0, count($accessList), true)); // key is access type, value is true
}

$env = Request::getInstance()->getEnvironment();
$smarty->assign("lc", $lc);
$smarty->assign("_ENV", $env->getName());
$smarty->assign("accesslist", $accessAssocArray);
$invalidEmailAddressManager = new InvalidEmailAddressManager($player);
$smarty->assign("isEmailValid", $invalidEmailAddressManager->hasValidEmail());
$smarty->assign("experimentalUiChanges", intval(PlayerSettings::getInstance($player)->get(PlayerSettings::EXPERIMENTAL_UI_CHANGES) == 1));

JsTranslations::getManager()->addTags(["js_chars_with_new_events"]);

$smarty->displayLang("page.player.tpl", $lang_abr);
