<?php
$smarty = new CantrSmarty;

//SANITIZE INPUT
$username = $_REQUEST['username'];
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$email = $_REQUEST['email'];
$year = $_REQUEST['year'];


$country = $_REQUEST['country'];

$reference = $_REQUEST['reference'];
$comment = $_REQUEST['comment'];
$charname1 = $_REQUEST['charname1'];
$refplayer = $_REQUEST['refplayer'];


// list of possible languages for characters
$languagesArray = array();
$spawnableLanguagesArray = array();
$db = Db::get();
$stm = $db->query("SELECT id, name, spawning_allowed FROM languages");

foreach ($stm->fetchAll() as $lang_info) {
  $lTag = new tag("<CANTR REPLACE NAME=lang_$lang_info->name>");
  $languagesArray[$lang_info->id] = $lTag->interpret();
  if ($lang_info->spawning_allowed) {
    $spawnableLanguagesArray[$lang_info->id] = $lTag->interpret();
  }
}
// check if current page language can be used for new characters
$chlang = (isset($spawnableLanguagesArray[$l])) ? $l : LanguageConstants::ENGLISH;

$language = HTTPContext::getInteger('language', $l);

$sex1 = HTTPContext::getInteger('sex1', 0);

$englishLanguageNames = Pipe::from(LanguageConstants::$LANGUAGE)->map(function($langData) {
  return $langData["en_name"];
})->toArray();

asort($languagesArray, SORT_NATURAL);
asort($spawnableLanguagesArray, SORT_NATURAL);

$smarty->assign("referrer", $referrer);
$smarty->assign("username", $username);
$smarty->assign("firstname", $firstname);
$smarty->assign("lastname", $lastname);
$smarty->assign("email", $email);
$smarty->assign("year", $year);
$smarty->assign("country", $country);
$smarty->assign("languages", $languagesArray);
$smarty->assign("language", $language);
$smarty->assign("l", $l);
$smarty->assign("refplayer", $refplayer);
$smarty->assign("reference", $reference);
$smarty->assign("comment", $comment);
$smarty->assign("charname1", $charname1);
$smarty->assign("sex1", $sex1);
$smarty->assign("spawnableLanguages", $spawnableLanguagesArray);
$smarty->assign("englishLanguageNames", json_encode($englishLanguageNames));

$smarty->displayLang("form.adduser.tpl", $lang_abr);

$stats = new Statistic("newplayer", Db::get());
$stats->update("onform", 1);


JsTranslations::getManager()->addTags([
  "register_error_field_required", "register_error_username_already_exists",
  "register_error_invalid_email_format", "register_error_email_already_exists",
  "register_error_password_min_length", "register_error_passwords_not_match",
  "register_error_year_format", "register_error_character_name_format",
  "register_step_game_rules", "plain_button_next", "button_register",
]);
