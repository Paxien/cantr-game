<?php

$lang = HTTPContext::getInteger('lang', 1);

$plr = Request::getInstance()->getPlayer();
if (!$plr->hasAccessTo(AccessConstants::SEE_ADMIN_DATA)) {
  CError::throwRedirect("player", "You are not authorized to access admin data page");
}

$g = new SpawnPointGenerator([], Db::get());
$pop = $g->getAttractivenessOfRootLocations($lang);

arsort($pop);
echo "<div class='page'>";
echo "Town attractiveness ranking for language=$lang. Controlled by GET parameter 'lang'.<br>";

echo "<pre>";
echo "value\tlocId\ttown name\n";
$oldLocNameManager = new OldLocNameManager(Db::get());
foreach ($pop as $town => $value) {
  $r = $oldLocNameManager->getUsersnameById($town);
  echo "$value\t$town\t$r\n";
}
echo "</pre>";


echo "<a href=\"index.php?page=player\">Back to player page</a>";
echo "</div>";
