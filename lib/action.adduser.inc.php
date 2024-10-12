<?php

$user = new user;

//SANITIZE INPUT
$user->username = trim(HTTPContext::getString('username', ''));
$user->firstname = HTTPContext::getString('firstname', '');
$user->lastname = HTTPContext::getString('lastname', '');
$user->email = HTTPContext::getString('email', '');
$user->age = HTTPContext::getInteger('year', 0);

$user->country = HTTPContext::getString('country', '');

$user->password = $_REQUEST['password'];
$user->password_retype = $_REQUEST['password_retype'];
$user->reference = HTTPContext::getString('reference', '');
$user->comment = HTTPContext::getString('comment', '');
$user->charname1 = HTTPContext::getString('charname1', null);
$user->language = HTTPContext::getInteger('language', 1);
$user->sex1 = HTTPContext::getInteger('sex1', 0);
$user->refplayer = HTTPContext::getRawString('refplayer');
$user->referrer = htmlspecialchars(base64_decode(strtr(HTTPContext::getString('referrer', 'unknown'), '-_,', '+/=')));

$stats = new Statistic('newplayer', Db::get());
$stats->update("filled_form", 1);

if ($user->validate()) {
    $registration = new PlayerRegistration();
    $registration->perform($user);
    redirect("login", [
      "id" => urlencode($user->username),
      "password" => urlencode($user->password),
      "data" => "yes"]);
  exit;
} else {
  //to stop make users angry on game start.. we don't redirect, just load
  //last page - with erro message, but with all fields set the same as before.

  error_bar($user->error, $lang_abr);

  unset($user);
  include "form.adduser.inc.php";
}
