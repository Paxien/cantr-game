<?php

$form_data = $_REQUEST['form_data'];
$pw = $_REQUEST['pw'];
$player_id = $_REQUEST['player_id'];
$file = trim($_REQUEST['file']);
$enc = $_REQUEST['enc'];

$accessType = ((_ENV == 'www') ? "44" : "43");

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo($accessType)) {
  CError::throwRedirect("player", "You are not authorized to access util.tester");
}

$env = Request::getInstance()->getEnvironment();

$allowed_on_test = array(
  'util/util.fixradios.inc.php' => array('?', '[run]', 'Fixes out of sync radios and locations with NULL coordinates (related issue)'),
  'util/util.synchroniseanimals.php' => array('no', '[run]', 'Synchronises the animals. Adds a row to translation db if it doesn\'t exist.'),
  'server/server.animals_manage.php' => array('?', '[run]', 'Idles out player, kills their chars etc.'),
  'server/server.cleanup.inc.php' => array('?', '[run]', 'Idles out player, kills their chars etc.'),
  'server/server.die.inc.php' => array('?', '[run]', 'Kills characters due to health, hunger and thirst'),
  'server/server.food.inc.php' => array('?', '[run]', 'Eating'),
  'server/server.projects.inc.php' => array('some', '[run]', 'Will advance the projects one hour in time. Does not affect the game time, only projects.'),
  'server/server.sailing.inc.php' => array('yes', '[run]', 'Increase all sailing/docking one turn.'),
  'server/server.travel.inc.php' => array('yes', '[run]', 'Increase all land based travel by one turn.'),
  'server/server.turn.finish.inc.php' => array('?', '[run]', 'Turn finish'),
  'server/server.animals_food.php' => array('some', '[run]', 'Domesticated animals fodder eating'),
  'server/server.deterioration.php' => array('?', '[run]', 'Deterioration'),
  'server/server.taint.php' => array('?', '[run]', 'Universal taint of raws'),
  'server/server.weather.php' => array('Yes', '[run]', 'Weather'),
  'server/server.messengers.php' => array('Yes', '[run]', 'Run messenger birds'),
  'util/util.reducetiredness.php' => array('no', '[run]', 'set tiredness of everyone to 0'),
  'util/util.reset_violence.php' => array('no', '[run]', 'Reset attack cooldown against both chars and animals'),
  'util/util.reset_project_requirements.php' => array('no', '[run]', 'Remove all requirements from existing projects'),
  'util/util.shownames.php' => array('no', '[run]', 'set own char name as name visible for others in loc 636'),
);

$allowed_on_live = array(
  'util/util.duplicate_intro_database.inc.php' => array('?', '[run]', 'Destroy all data on intro db (except: players, spawnpoints) and fill by some clear, test data.'),
  'util/util.duplicate_test_database.inc.php' => array('?', '[run]', 'Destroy all data in TE db and fill by some clear, test data.'),
  'util/util.fixradios.inc.php' => array('?', '[run]', 'Fixes out of sync radios and locations with NULL coordinates (related issue)'),
  'util/util.synchroniseanimals.php' => array('no', '[run]', 'Synchronises the animals. Adds a row to translation db if it doesn\'t exist.'),
);

echo "<center>";
echo "<strong>WARNING</strong>: Use this utility only very carefully - results do influence the ";
if (_ENV == 'www') {
  echo "LIVE";
  $allowed = $allowed_on_live;
} else {
  echo "test";
  $allowed = $allowed_on_test;
}
echo " database.<br /><br />";
echo "</center>";
echo "<strong>  &nbsp; Available scripts</strong><br />";
echo "<table style=\"margin-left: 15px;\" border=\"1\" cellpadding=\"2\">";
echo "<tr><th align=\"left\">Script</th><th align=\"left\">Lags</th><th align=\"left\">Run</th><th align=\"left\">Info</th></tr>";

foreach ($allowed as $script => $data) {
  echo "<tr><td>" . $script . "</td><td>" . $data[0] . "</td><td>" . $data[1] . "</td><td><em>" . $data[2] . "</em></td></tr>";
}

echo "</table>";
echo "<center><br /><br />";
# Function stolen from http://us2.php.net/manual/en/function.get-include-path.php
function add_include_path($path)
{
  foreach (func_get_args() AS $path) {
    if (!file_exists($path) OR (file_exists($path) && filetype($path) !== 'dir')) {
      trigger_error("Include path '{$path}' not exists", E_USER_WARNING);
      continue;
    }

    $paths = explode(PATH_SEPARATOR, get_include_path());

    if (array_search($path, $paths) === false)
      array_push($paths, $path);

    set_include_path(implode(PATH_SEPARATOR, $paths));
  }
}

$client_ip = 0;
$db = Db::get();
if (isset($form_data)) {
  if (!array_key_exists($file, $allowed)) {
    echo "<div class=\"admin_error\">Error: That script can not be run.</div>";
  } else {

    // SANTITIZE INPUT

    if (is_numeric($player_id)) {
      $player_id = intval($player_id);
    } else {
      $stm = $db->prepare("SELECT id FROM players WHERE username = :username");
      $stm->bindStr("username", $player_id);
      $player_id = $stm->executeScalar();
    }

    add_include_path(getcwd() . "/../lib");
    add_include_path(getcwd() . "/../lib/server");

    if ($env->is("main") && $player_id != $player) {
      die("Access refused!");
    }

    $dbName = $env->getDbNameFor($env->getName());
    $ut_msg = "Player ID: " . $playerInfo->getFullNameWithId() . ")\nDatabase: " . $dbName . "\nScript: $file\n";

    // Get information about user
    $remaddr = $GLOBALS['REMOTE_ADDR'];
    $remhost = gethostbyaddr($remaddr);

    $date = date("d/m/Y H:i");
    $info = "$date $remaddr ($remhost)";

    $headers = emu_getallheaders();

    if (isset($headers["Client-IP"]))
      $client_ip = $headers["Client-IP"];

    if (isset($headers["X-Forwarded-For"]))
      $client_ip = $headers["X-Forwarded-For"];

    $ut_msg .= "\n\n$info\n\n";

    foreach ($headers as $header => $value)
      $ut_msg .= "$header: $value\n";

    $ut_msg .= "\n\n";

  if ($enc) {
    $pw = urldecode($pw);
  }

  if (!$env->is('main') || SecurityUtil::verifyPassword($pw, $playerInfo->getPasswordHash())) {

      $ut_msg .= "Access granted\n";

      $stm = $db->query("SELECT * FROM turn");
      $turn = $stm->fetchObject();

      echo "<pre>";
      include $file;
      echo "</pre>";

    } else {

      $ut_msg .= "Access denied\n";

      echo "Incorrect password!";
    }
  }

  $message = date('Y-m-d H:i:s', time()) . ',Player[' . $playerInfo->getFullNameWithId() .
    '],IP[' . $remaddr . '(' . $remhost . ')],File[' . $file . "]\n";
  error_log($message, 3, $env->absoluteOrRelativeToRootPath($env->getConfig()->getTesterScriptsLogFile()));

}

echo "<form method=\"post\" action=\"index.php?page=utiltester\"><table>";
echo "<tr><td>Name php file: </td><td><input type=text name=file value='$file' /></td></tr>";
if ($env->is("main")) {
  echo "<tr><td>Player ID: </td><td><input type=text name=player_id /></td></tr>";
  echo "<tr><td>Password: </td><td><input type=password name=pw /></td></tr>";
}
echo "<tr><td>&nbsp;</td><td><input type=submit value=Submit></td></tr>";
echo "<input type=hidden value=yes name=form_data>";
echo "</table></form>";

echo "<p><a href=\"index.php?page=player\">Back to player page</a>";
echo "</center>";
