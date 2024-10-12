<?php

$imageId = HTTPContext::getRawString("img", null);
$accept = HTTPContext::getInteger('accept');

include_once("../www/pictures/settings.php");

$db = Db::get();
$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::NOTES_MANIPULATION)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

/**
 * @param $imageName
 */
function moveImageToRefused($imageName)
{
  rename(_ROOT_LOC . "/user_assets/uploaded_images/$imageName",
    _ROOT_LOC . "/user_assets/refused_images/$imageName");
}

if ($imageId) {
  $stm = $db->prepare("SELECT name FROM player_images WHERE id = :id AND accepted = 0 LIMIT 1");
  $stm->bindInt("id", $imageId);
  $imageName = $stm->executeScalar();
  if ($imageName) {
    // 0 - awaiting, 1 - yes, 2 - no
    $stm = $db->prepare("UPDATE player_images SET accepted = :verdict, accepted_by = :adminId, date = NOW() WHERE id = :id LIMIT 1");
    $stm->bindInt("verdict", $accept);
    $stm->bindInt("adminId", $playerInfo->getId());
    $stm->bindInt("id", $imageId);
    $stm->execute();
    if ($accept == 2) { // refuse file
      moveImageToRefused($imageName);
    }
  }
}

$stm = $db->query("SELECT * FROM player_images WHERE accepted = 0 ORDER BY date LIMIT 100");
$imgs = $stm->fetchAll();

$smarty = new CantrSmarty;
$smarty->assign("imgs", $imgs);
$smarty->displayLang("page.pd_image_review.tpl", $lang_abr);
