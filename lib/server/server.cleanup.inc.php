<?php

$page = "server.cleanup";
include "server.header.inc.php";

print "Cleanup:\n";

$db = Db::get();
$mailService = new MailService("Cantr Accounts", $GLOBALS['emailPlayers']);
$environment = Request::getInstance()->getEnvironment();
$playerCleanupManager = new PlayerCleanupManager($db, $mailService, $environment);

$playerCleanupManager->processAll(35, 22, 3);

print "done.\n";

include "server/server.footer.inc.php";
