<?php
require_once '../vendor/autoload.php';
(new Raven_Client('https://ab2a8198a56e4d38bb154ec0450509a5@o392706.ingest.sentry.io/5240679'))->install();

// When we have downtime, uncomment the line below, and _copy_ ../storage/index.html.downtime to index.html.
// When the game is back up, comment the line below and delete index.html.
//header("Location: http://cantr.net");
// start time measuring

list($usec, $sec) = explode(" ", microtime());
$time = $usec + $sec % 10000000000;

include_once "../lib/stddef.inc.php";
require_once _LIB_LOC . "/header.functions.inc.php";

checkIsDBAvailable(); // Database is opened here

$s = session::getSessionFromCookie();
if ($s && !Session::sessionExistsInDatabase($s)) {
  $s = null; // session has expired despite having a cookie
}

require_once _LIB_LOC . "/func.getheaders.inc.php";
require_once _LIB_LOC . "/func.genes.inc.php";

require_once _LIB_LOC . "/urlencoding.inc.php";
DecodeURIs();

require_once _LIB_LOC . "/func.getdirection.inc.php";
require_once _LIB_LOC . "/func.getrandom.inc.php";

$db = Db::get();
$turn = GameDate::NOW()->getObject();
$turn_info = clone $turn; // Just to be on the safe side

// SANITIZE INPUT
$page = HTTPContext::getString('page', '');
$data = $_REQUEST['data'];
$error = $_REQUEST['error'];
$character = HTTPContext::getInteger('character', null);

if ($s) {
  //aktualize cookie - for cancel session expiring counter
  session::updateCookie($s);
}

if (Validation::isInt($_REQUEST['l'])) {
  $l = HTTPContext::getInteger('l');
} elseif ($_REQUEST['l']) {
  $l = LanguageConstants::$ID_FOR_ABBR[$_REQUEST['l']];
}

if (!$l) {
  $l = LanguageConstants::$ID_FOR_ABBR["en"];
}

updateLanguageCookie($l);

$referrer = HTTPContext::getString('referrer', null);
if (!$referrer) {
  $referrer = ($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "unknown";
  $referrer = strtr(base64_encode($referrer), '+/=', '-_,');
}

$outerPages = array("rules", "passreminder", "login", "adduser", "departments", "privacy", "terms", "cookies",
  "intro", "constitution", "helppage", "faq", "afterapplication", "activate_invalid_email", "send_reactivation_email");

if ($page && !in_array($page, $outerPages)
  && (isset($s) || (!in_array($page, ["contact", "irc"])))
) {
  $session_handle = new session;
  $session_handle->session_id = $s;
  $session_handle->session_char = $character;
  $session_info = $session_handle->checklogin();

  $player = $session_info->player;
  $l = $session_handle->language;
  $lang_abr = $session_handle->languagestr;

  if ((isset($character)) and !empty($character)) {
    $l = Character::loadById($character)->getLanguage();
  }

} else {

  $l = ($l) ? $l : 1;
  if (isset($s)) {
    $s = intval($s);
  }
  if (!$lang_abr) {
    $lang_abr = LanguageConstants::$LANGUAGE[$l]['lang_abr'];
  }
}

$activeminutes = 15;
$usersCount = Session::getActivePlayersCount(time(), $activeminutes);

include_once _LIB_LOC . "/smarty.inc.php";
$smartyMain = new CantrSmarty;
$smartyMain->assign("turn", $turn);
$smartyMain->assign("USERSCOUNT", $usersCount);
$smartyMain->assign("ACTIVEPERIOD", $activeminutes);

// pages which use prototype.js
$nonJQueryPages = array("ce");

if (!in_array($page, $nonJQueryPages)) {
  $smartyMain->assign("jQueryNeeded", true);
}

// React components integration
$reactIntegrationManager = new ReactIntegrationManager();
$smartyMain->assign("reactJs", $reactIntegrationManager->getReactJsFiles());
$smartyMain->assign("reactCss", $reactIntegrationManager->getReactCssFiles());
JsTranslations::getManager()->addTags($reactIntegrationManager->getTagsNeededByReact());


$smartyMain->assign("devserverMode", $env->getConfig()->devserverMode());

if (isset($character) && PlayerSettings::getInstance($player)->get(PlayerSettings::JS_LOCATION) == 0
) {
  $smartyMain->assign("useLocationExtendedBox", true);
  JsTranslations::getManager()->addTags([
    'alt_alter_sign',
    'alt_pointat_building',
    'alt_enter_building_or_room',
    'alt_knock_door',
    'alt_pointat_road',
    'alt_follow_exit',
    'desc_chars_left',
    'js_box_close',
    'js_location_has_contents',
    'js_location_name_label',
    'js_location_rename',
    'js_location_custom_desc',
  ]);
}

$request = Request::getInstance();
$env = $request->getEnvironment();
$smartyMain->assign("ENV", $env);
if ($env->is("intro")) {
  $smartyMain->assign("showTooltips", true);
  $smartyMain->assign("pageName", $page);
}

$urlToRedirect = $env->getConfig()->getUrlToRedirectWhenNoCharacter();
if (!empty($urlToRedirect)) {
  $shouldRedirect = empty($character) && !($page === "login" && isset($data));
  $canAvoidRedirect = $request->isPlayerSpecified()
    && $request->getPlayer()->hasAccessTo(AccessConstants::AVOID_REDIRECT_ON_NON_CHARACTER_PAGE);

  if ($shouldRedirect && !$canAvoidRedirect) {
    cantr_redirect($urlToRedirect . "?page=$page");
    exit();
  }
}

ob_start();

if ($error) {
  error_bar($error, $lang_abr);
}

$permitWhenLocked = array("logout", "player", "sendpm", "infoplayer", "irc", "contact",
  "unsubscribe", "cancel_unsub_counter", "requestreport", "seen_message", "publicstatistics",
  "settings", "departments", "afterapplication", "login_on_intro");
if ($page && !in_array($page, $outerPages) && !in_array($page, $permitWhenLocked)
) {
  $playerStatus = Player::loadById($player)->getStatus();
  if ($playerStatus == PlayerConstants::LOCKED) {
    CError::throwRedirectTag("player", "error_account_locked");
  }
}

if ($page && !in_array($page, $outerPages) && !in_array($page, ["reactivation", "contact", "logout"])) {
  $playerStatus = Player::loadById($player)->getStatus();
  if (in_array($playerStatus, [PlayerConstants::UNSUBSCRIBED, PlayerConstants::IDLEDOUT])) {
    redirect("reactivation");
    exit();
  }
}

switch ($page) {

  case "reactivation":
    include _LIB_LOC . "/action.reactivation_request.php";
    break;

  case "activate_invalid_email":
    include _LIB_LOC . "/page.activate_invalid_email.php";
    break;

  case "send_reactivation_email":
    include _LIB_LOC . "/page.send_reactivation_email.php";
    break;

  case "send_email_confirmation":
    include _LIB_LOC . "/action.send_email_confirmation.php";
    break;

  case "login_on_intro":
    include _LIB_LOC . "/action.login_on_intro.php";
    break;

  case "tplman":
    include _LIB_LOC . "/templatemgr.php";
    break;

  case "manual_events":
    if ($data) {
      include _LIB_LOC . "/action.manual_events.php";
    } else {
      include _LIB_LOC . "/page.manual_events.php";
    }
    break;

  case "addchar" :
    if ($data) {
      include _LIB_LOC . "/action.addchar.inc.php";
    } else {
      include _LIB_LOC . "/form.addchar.inc.php";
    }
    break;

  case "manage_events":
    if ($data) {
      include _LIB_LOC . "/rd_tools/action.manage.eventsgroups.inc.php";
    } else {
      include _LIB_LOC . "/rd_tools/page.manage.eventsgroups.inc.php";
    }
    break;

  case "manage_privs" :
    include _LIB_LOC . "/page.manage.privileges.inc.php";
    break;

  case "apply" :
    include _LIB_LOC . "/page.apply.inc.php";
    break;

  case "faq" :
    include _LIB_LOC . "/page.faq.php";
    break;

  case "mailall" :
    include _LIB_LOC . "/page.mailall.inc.php";
    break;

  case "manage_animals" :
    include _LIB_LOC . "/rd_tools/page.manage_animals.inc.php";
    break;

  case "constitution" :
    include _LIB_LOC . "/info.constitution.inc.php";
    break;

  case "rules":
    include _LIB_LOC . "/page.rules.php";
    break;

  case "privacy" :
    include _LIB_LOC . "/info.privacy.php";
    break;

  case "terms" :
    include _LIB_LOC . "/info.terms.php";
    break;

  case "cookies" :
    include _LIB_LOC . "/info.cookies.php";
    break;

  case "adduser" :
    if ($data) {
      include _LIB_LOC . "/action.adduser.inc.php";
    } elseif ($_REQUEST['tick']) {
      include _LIB_LOC . "/form.adduser.inc.php";
    } else {
      include _LIB_LOC . "/info.beforeaccount.inc.php";
    }
    break;

  case "links" :
    include _LIB_LOC . "/page.links.inc.php";
    break;

  case "surveylist" :
    include _LIB_LOC . "/page.survey_list.inc.php";
    break;

  case "managesurvey" :
    include _LIB_LOC . "/page.survey_manage.inc.php";
    break;

  case "submitsurvey" :
    include _LIB_LOC . "/action.submit_survey.inc.php";
    break;

  case "createsurvey" :
    include _LIB_LOC . "/page.create_survey.inc.php";
    break;

  case "addraw" :
    include _LIB_LOC . "/action.addraw.inc.php";
    break;

  case "afterapplication" :
    include _LIB_LOC . "/page.afterapplication.inc.php";
    break;

  case "departments" :
    include _LIB_LOC . "/info.departments.inc.php";
    break;

  case "notes_log" :
    include _LIB_LOC . "/page.notes_log.php";
    break;

  case "delraw" :
    include _LIB_LOC . "/rd_tools/action.delraw.inc.php";
    break;

  case "irc" :
    include _LIB_LOC . "/page.irc.inc.php";
    break;

  case "settings" :
    include _LIB_LOC . "/page.settings.inc.php";
    break;

  case "financessummary" :
    include _LIB_LOC . "/statistics/info.financessummary.inc.php";
    break;

  case "statistics" :
    include _LIB_LOC . "/statistics/info.statistics.inc.php";
    break;

  case "publicstatistics" :
    include _LIB_LOC . "/statistics/info.publicstatistics.inc.php";
    break;

  case "seen_message" :
    include _LIB_LOC . "/action.seen_message.inc.php";
    break;

  case "lockgame" :
    include _LIB_LOC . "/action.lockgame.inc.php";
    break;

  case "kill" :
    include _LIB_LOC . "/action.kill.inc.php";
    break;

  case "listplayers" :
    include _LIB_LOC . "/info.listplayers.inc.php";
    break;

  case "multiaccount_tracker" :
    include _LIB_LOC . "/admin/page.cookieless_tracker.php";
    break;

  case "listlimitations":
    include _LIB_LOC . "/admin/info.limitations.php";
    break;

  case "admindata":
    include _LIB_LOC . "/admin/page.admindata.php";
    break;

  case "listevents" :
    include _LIB_LOC . "/info.listevents.inc.php";
    break;

  case "managemachines" :
    if ($data) {
      include _LIB_LOC . "/rd_tools/action.store_machine.inc.php";
    } elseif ($_REQUEST['kopy']) {
      include _LIB_LOC . "/rd_tools/action.copy_machine.inc.php";
    } else {
      include _LIB_LOC . "/rd_tools/page.manage_machines.inc.php";
    }
    break;

  case "passreminder" :
    include _LIB_LOC . "/page.password_reminder.php";
    break;

  case "manageclothescategories" :
    include _LIB_LOC . "/rd_tools/page.manage_clothes_categories.inc.php";
    break;

  case "managevehicles" :
    if ($data) {
      include _LIB_LOC . "/rd_tools/action.store_vehicles.inc.php";
    } else {
      include _LIB_LOC . "/rd_tools/page.manage_vehicles.inc.php";
    }
    break;

  case "manageobjects" :
    include _LIB_LOC . "/rd_tools/manage_objects.php";
    break;

  case "managerawtypes" :
    if ($_REQUEST['data2']) {
      include _LIB_LOC . "/rd_tools/action.store_rawtool.inc.php";
    } elseif ($data) {
      include _LIB_LOC . "/rd_tools/action.store_rawtype.inc.php";
    } else {
      include _LIB_LOC . "/rd_tools/page.manage_rawtypes.inc.php";
    }
    break;

  case "managetranslations" :
    include _LIB_LOC . "/page.manage_translations.inc.php";
    break;

  case "infoplayer" :
    if ($data) {
      include _LIB_LOC . "/action.alter_player_password.inc.php";
    } elseif ($_REQUEST['change_email']) {
      include _LIB_LOC . "/action.alter_player_email.inc.php";
    } elseif ($_REQUEST['switch_locking_status']) {
      include _LIB_LOC . "/action.switch_locking_status.inc.php";
    } elseif ($_REQUEST['change_notes']) {
      include _LIB_LOC . "/action.update_player_notes.inc.php";
    } elseif ($_REQUEST['switch_limitations']) {
      include _LIB_LOC . "/action.switch_limitation.inc.php";
    } else {
      include _LIB_LOC . "/info.infoplayer.php";
    }
    break;

  case "travels_timeline":
    include _LIB_LOC . "/pd_tools/page.travels_timeline.php";
    break;

  case "indirectobjecttransfers":
    include _LIB_LOC . "/pd_tools/page.indirect_object_transfers.php";
    break;

  case "radioreport" :
    include _LIB_LOC . "/util.radioreport.inc.php";
    break;

  case "lock_char":
    include _LIB_LOC . "/action.lock_char.php";
    break;

  case "lock_newchars":
    include _LIB_LOC . "/action.lock_newchars.php";
    break;

  case "infoloc":
    include _LIB_LOC . "/info.location.inc.php";
    break;

  case "researchplayers" :
    include _LIB_LOC . "/researchplayers.inc.php";
    break;

  case "doubleaccts" :
    include _LIB_LOC . "/doubleaccts.inc.php";
    break;

  case "sendpm" :
    if (isset($_REQUEST['message_text'])) {
      include _LIB_LOC . "/action.sendpm.inc.php";
    } else {
      include _LIB_LOC . "/form.sendpm.inc.php";
    }
    break;

  case "remove_message" :
    include _LIB_LOC . "/action.remove_message.inc.php";
    break;

  case "remove_all_messages" :
    include _LIB_LOC . "/action.remove_all_messages.inc.php";
    break;

  case "pendingplayers" :
    include _LIB_LOC . "/info.pendingplayers.inc.php";
    break;

  case "listraws" :
    if ($_REQUEST['region']) {
      include _LIB_LOC . "/rd_tools/info.listraws.inc.php";
    } else {
      $showlang = false;
      $showempty = true;
      include _LIB_LOC . "/rd_tools/form.selectregion.inc.php";
    }
    break;

  case "listanimals":
    if ($_REQUEST['region']) {
      include _LIB_LOC . "/rd_tools/info.listanimals.inc.php";
    } else {
      $showlang = true;
      $showempty = false;
      include _LIB_LOC . "/rd_tools/form.selectregion.inc.php";
    }
    break;

  case "alteranimals":
    include _LIB_LOC . "/rd_tools/action.alteranimals.inc.php";
    break;

  case "listlocations" :
    if ($_REQUEST['region']) {
      include _LIB_LOC . "/rd_tools/info.listlocations.inc.php";
    } else {
      $showlang = false;
      $showempty = false;
      include _LIB_LOC . "/rd_tools/form.selectregion.inc.php";
    }
    break;

  case "listlocationspd" :
    include _LIB_LOC . "/info.listlocationspd.inc.php";
    break;

  case "vehicleapplet" :
    include _LIB_LOC . "/rd_tools/page.vehicleapplet.inc.php";
    break;

  case "login" :
    if ($data) {
      include _LIB_LOC . "/action.login.inc.php";
    } else {
      include _LIB_LOC . "/form.login.inc.php";
    }
    break;

  case "logout" :
    include _LIB_LOC . "/action.logout.inc.php";
    break;

  case "messageallplayers" :
    if ($data) {
      include _LIB_LOC . "/action.message_all_players.inc.php";
    } else {
      include _LIB_LOC . "/form.message_all_players.inc.php";
    }
    break;

  case "player" :
    include _LIB_LOC . "/page.player.inc.php";
    break;
  case "":
  case "intro" :
    include _LIB_LOC . "/intro.php";
    break;

  case "unsubscribe" :
    if ($data) {
      include _LIB_LOC . "/action.unsubscribe.inc.php";
    } else {
      include _LIB_LOC . "/form.unsubscribe.inc.php";
    }
    break;

  case "cancel_unsub_counter":
    include _LIB_LOC . "/action.cancel_unsub_counter.php";
    break;

  case "manageadvert" :
    include _LIB_LOC . "/page.manage_advertisement.inc.php";
    break;

  case "ce" :
    include "ce.inc.php";
    break;

  case "requestreport" :
    include _LIB_LOC . "/action.sendturnreport.inc.php";
    break;

  case "contact" :
    if ($data) {
      include _LIB_LOC . "/action.contact.inc.php";
    } else {
      include _LIB_LOC . "/form.contact.inc.php";
    }
    break;

  case "utiltester" :
    include _LIB_LOC . "/util.tester.php";
    break;

  case "votinglinks" :
    include _LIB_LOC . "/page.manage_votinglinks.inc.php";
    break;

  case "pdreadnote" :
    include _LIB_LOC . "/info.pd_readnote.php";
    break;

  case "pdimagereview" :
    include _LIB_LOC . "/page.pd_image_review.php";
    break;

  case "publicdesc" :
    include _LIB_LOC . "/action.describe.inc.php";//this is called from char description or pd panel
    break;

  default:

    // character actions
    if (Player::loadById($player)->isOnLeave()) {
      CError::throwRedirectTag("player", "error_play_while_on_leave");
    }

    try {
      $char = Character::loadById($character);
    } catch (InvalidArgumentException $e) {
      CError::throwRedirect("player", "<CANTR REPLACE NAME=error_page_unavailable PAGE=$page> (no character)");
    }

    if ($char->isPending()) { // character is entered for the first time
      $char->activateSpawnedCharacter();
      $char->saveInDb();
    }

    if (!$char->isAlive()) {
      CError::throwRedirectTag("player", "error_play_dead_char");
    }

    if (Limitations::getLims($character, Limitations::TYPE_LOCK_CHAR) > 0) {
      CError::throwRedirectTag("player", "error_character_locked");
    }

    $smartyMain->assign("ownCharName", $char->getName());
    $smartyMain->assign("inventoryWeight", $char->getInventoryWeight());

    switch ($page) {

      case 'custom_event':
        include _LIB_LOC . "/action.custom_event.php";
        break;

      case "characterdescription":
        include _LIB_LOC . "/page.characterdescription.inc.php";
        break;

      case "name" :
        include _LIB_LOC . "/action.name.inc.php";
        break;

      case "changebuildingdesc" :
        include _LIB_LOC . "/action.change_building_description.php";
        break;

      case "changeobjdesc":
        if ($data) {
          include _LIB_LOC . "/action.change_object_description.php";
        } else {
          include _LIB_LOC . "/page.change_object_description.php";
        }
        break;

      case "publicdesc_guide" :
        include _LIB_LOC . "/info.public_desc.inc.php";
        break;

      case "objdesc_guide":
        include _LIB_LOC . "/info.object_desc.php";
        break;

      case "char" :
        include _LIB_LOC . "/page.events.inc.php";
        break;

      case "char.settings" :
        if ($data == "yes") {
          include _LIB_LOC . "/settings/action.charsettings.inc.php";
        } else {
          include _LIB_LOC . "/settings/page.charsettings.inc.php";
        }
        break;

      case "death_old_age":
        if ($data) {
          include _LIB_LOC . "/action.death_old_age.php";
        } else {
          include _LIB_LOC . "/page.death_old_age.php";
        }
        break;

      case "talk" :
        if ($data) {
          include _LIB_LOC . "/action.talk.inc.php";
        } else {
          include _LIB_LOC . "/form.talk.inc.php";
        }
        break;

      case "char.events" :
        include _LIB_LOC . "/page.events.inc.php";
        break;

      case "char.description" :
        include _LIB_LOC . "/page.description.inc.php";
        break;

      case "char.people" :
        include _LIB_LOC . "/page.people.inc.php";
        break;

      case "suicide" :
        if ($data) {
          include _LIB_LOC . "/action.suicide.php";
        } else {
          include _LIB_LOC . "/page.suicide.php";
        }
        break;

      case "pointat" :
        include _LIB_LOC . "/action.pointat.inc.php";
        break;

      case "reportabuse" :
        include _LIB_LOC . "/form.report_abuse.inc.php";
        break;

      case "reportdescription" :
        if ($data) {
          include _LIB_LOC . "/action.report_description.php";
        } else {
          include _LIB_LOC . "/page.report_description.php";
        }
        break;

      default :
        //I had to move these here because it's not possible to exclude cases with an if/else structure

        if ($char->hasPassedOut()) { //character is incapacitated, cannot do most things
          CError::throwRedirectTag("char", "error_too_drunk");
        } elseif ($char->isNearDeath()) { // character is in near death state, cannot do most things
          CError::throwRedirectTag("char", "error_near_death_state");
        } else {
          switch ($page) {
            case "adjustsailing" :
              include _LIB_LOC . "/action.adjustsailing.inc.php";
              break;

            case "build" :
              if ($_REQUEST['objecttype']) {
                if ($data) {
                  include _LIB_LOC . "/action.build.inc.php";
                } else {
                  include _LIB_LOC . "/form.build.inc.php";
                }
              } else { // if not chosen
                include _LIB_LOC . "/build.inc.php";
              }
              break;

            case "bury" :
              include _LIB_LOC . "/action.bury.inc.php";
              break;

            case "canceldocking" :
              include _LIB_LOC . "/action.canceldocking.inc.php";
              break;

            case "char.buildings" :
              include _LIB_LOC . "/page.buildings.inc.php";
              break;

            case "char.inventory" :
              include _LIB_LOC . "/page.inventory.inc.php";
              break;
            case "char.objects" :
              include _LIB_LOC . "/page.objects.inc.php";
              break;
            case "char.projects" :
              include _LIB_LOC . "/page.projects.inc.php";
              break;

            case "copykey":
              include _LIB_LOC . "/action.copykey.inc.php";
              break;

            case "copynote" :
              include _LIB_LOC . "/action.copynote.inc.php";
              break;

            case "copyseal":
              include _LIB_LOC . "/action.copyseal.inc.php";
              break;

            case "copy_book":
              include _LIB_LOC . "/action.copy_book.php";
              break;

            case "create_envelop" :
              if ($data) {
                include _LIB_LOC . "/action.create_envelop.inc.php";
              } else {
                include _LIB_LOC . "/form.create_envelop.inc.php";
              }
              break;

            case "animals" :
              if ($data) {
                include _LIB_LOC . "/animals/action.animals.domestication.php";
              } else {
                include _LIB_LOC . "/animals/page.animals.php";
              }
              break;

            case "animal_saddling":
              if ($data) {
                include _LIB_LOC . "/animals/steed/action.saddling.php";
              } else {
                include _LIB_LOC . "/animals/steed/page.saddling.php";
              }
              break;

            case "animal_unsaddling":
              if ($data) {
                include _LIB_LOC . "/animals/steed/action.unsaddling.php";
              } else {
                include _LIB_LOC . "/animals/steed/page.unsaddling.php";
              }
              break;

            case "animal_adopt":
              include _LIB_LOC . "/animals/action.animals.adopt.php";
              break;

            case "steed_adopt":
              include _LIB_LOC . "/animals/steed/action.adopt_steed.php";
              break;

            case "pack_join" :
              include _LIB_LOC . "/animals/action.animals.pack_join.php";
              break;

            case "animal_butcher" :
              if ($data) {
                include _LIB_LOC . "/animals/action.animals.butcher.php";
              } else {
                include _LIB_LOC . "/animals/page.animals.butcher.php";
              }
              break;

            case "usefurniture" :
              include _LIB_LOC . "/page.usefurniture.inc.php";
              break;


            case "delproject" :
              include _LIB_LOC . "/action.delproject.inc.php";
              break;

            case "dest" :
              include _LIB_LOC . "/action.destlock.inc.php";
              break;

            case "dig" :
              if ($data) {
                include _LIB_LOC . "/action.dig.inc.php";
              } else {
                include _LIB_LOC . "/form.dig.inc.php";
              }
              break;

            case "turn_into_messenger":
              include _LIB_LOC . "/animals/messengerBirds/action.turn_into_messenger.php";
              break;

            case "turn_back_from_messenger":
              include _LIB_LOC . "/animals/messengerBirds/action.turn_back_from_messenger.php";
              break;

            case "dock" :
              include _LIB_LOC . "/action.dock.inc.php";
              break;

            case "pull_out":
              include _LIB_LOC . "/action.pull_out.php";
              break;

            case "drag" :
              if ($data) {
                include _LIB_LOC . "/action.drag.inc.php";
              } else {
                include _LIB_LOC . "/page.drag.inc.php";
              }
              break;

            case "dropdragging" :
              include _LIB_LOC . "/action.dropdragging.inc.php";
              break;

            case "dropproject" :
              include _LIB_LOC . "/action.dropproject.inc.php";
              break;

            case "eatraw" :
              if ($data) {
                include _LIB_LOC . "/action.eatraw.inc.php";
              } else {
                include _LIB_LOC . "/form.eatraw.inc.php";
              }
              break;

            case "break_seal":
              if ($data) {
                include _LIB_LOC . "/action.break_seal.php";
              } else {
                include _LIB_LOC . "/form.break_seal.php";
              }
              break;

            case "destroy_envelop" :
              include _LIB_LOC . "/action.destroy_envelop.php";
              break;

            case "enter" :
              include _LIB_LOC . "/action.enter.inc.php";
              break;

            case "remove_note_duplicates" :
              include _LIB_LOC . "/action.remove_note_duplicates.php";
              break;

            case "move":
              include _LIB_LOC . "/action.move.php";
              break;

            case "executerepair" :
              include _LIB_LOC . "/action.repair.inc.php";
              break;

            case "multinotes" :
              if ($data) {
                include _LIB_LOC . "/action.fill_envelop.inc.php";
              } elseif ($_REQUEST['noteaction_submit'] == "store_in_storage") {
                include _LIB_LOC . "/page.multinotes_into_storage.php";
              } else {
                include _LIB_LOC . "/form.fill_envelop.inc.php";
              }
              break;

            case "multinotes_into_storage":
              include _LIB_LOC . "/action.multinotes_into_storage.php";
              break;

            case "give" :
              include _LIB_LOC . "/redirect.give.php"; // -> (action|form).give.php
              break;

            case "drop":
              include _LIB_LOC . "/redirect.drop.php"; // -> (action|form).drop.php
              break;

            case "take":
              include _LIB_LOC . "/redirect.take.php"; // -> (action|form).take.php
              break;

            case "help_char": // used to decide whether to help in project or dragging and redirects
              include _LIB_LOC . "/action.help_char.php";
              break;

            case "helpdrag" :
              include _LIB_LOC . "/page.helpdrag.inc.php";
              break;

            case "hit" :
              include _LIB_LOC . "/action.hit.inc.php";
              break;

            case "finish_off" :
              include _LIB_LOC . "/action.finish_off.php";
              break;

            case "heal_near_death" :
              include _LIB_LOC . "/action.heal_near_death.php";
              break;

            case "hitanimal" :
              include _LIB_LOC . "/action.hitanimal.inc.php";
              break;

            case "improve" :
              if ($data) {
                include _LIB_LOC . "/action.improve.inc.php";
              } else {
                include _LIB_LOC . "/form.improve.inc.php";
              }
              break;

            case "infoproject" :
              include _LIB_LOC . "/info.project.inc.php";
              break;

            case "joinproject" :
              include _LIB_LOC . "/action.joinproject.inc.php";
              break;

            case "boost_vehicle":
              include _LIB_LOC . "/action.join_boosting_vehicle.php";
              break;

            case "keytag":
              include _LIB_LOC . "/page.namekey.inc.php";
              break;

            case "knock" :
              include _LIB_LOC . "/action.knock.inc.php";
              break;

            case "lock" :
              include _LIB_LOC . "/action.lock.inc.php";
              break;

            case "openwindow" :
              include _LIB_LOC . "/action.open_window.php";
              break;

            case "loot" :
              include _LIB_LOC . "/action.lootbody.inc.php";
              break;

            case "matchspeed" :
              include _LIB_LOC . "/action.matchspeed.inc.php";
              break;

            case "nameloc" :
              include _LIB_LOC . "/page.nameloc.inc.php";
              break;

            case "picklock" :
              if ($data) {
                include _LIB_LOC . "/action.picklock.inc.php";
              } else {
                include _LIB_LOC . "/page.picklock.php";
              }
              break;

            case "playmusic" :
              include _LIB_LOC . "/action.playmusic.inc.php";
              break;

            case "promptrecycling" :
              include _LIB_LOC . "/form.promptrecycling.inc.php";
              break;

            case "purge" :
              include _LIB_LOC . "/action.purge.inc.php";
              break;

            case "radio" :
              include _LIB_LOC . "/page.radio.inc.php";
              break;

            case "readnote" :
              include _LIB_LOC . "/info.note.inc.php";
              break;

            case "recycling" :
              include _LIB_LOC . "/action.recycling.inc.php";
              break;

            case "repair" :
              include _LIB_LOC . "/form.repair.inc.php";
              break;

            case "destroy_building":
              if ($data) {
                include _LIB_LOC . "/action.destroy_building.php";
              } else {
                include _LIB_LOC . "/page.destroy_building.php";
              }
              break;

            case "repair_location":
              if ($data) {
                include _LIB_LOC . "/action.repair_location.php";
              } else {
                include _LIB_LOC . "/page.repair_location.php";
              }
              break;

            case "disassemble_vehicle":
              if ($data) {
                include _LIB_LOC . "/action.disassemble_vehicle.php";
              } else {
                include _LIB_LOC . "/page.disassemble_vehicle.php";
              }
              break;

            case "retrieve" :
              if ($data) {
                include _LIB_LOC . "/action.retrieve.php";
              } else {
                include _LIB_LOC . "/page.retrieve.inc.php";
              }
              break;

            case "seal" :
              if ($data) {
                include _LIB_LOC . "/action.seal.php";
              } else {
                include _LIB_LOC . "/page.seal.php";
              }
              break;

            case "seal_object":
              if ($data) {
                include _LIB_LOC . "/action.anonymous_seal.php";
              } else {
                include _LIB_LOC . "/form.anonymous_seal.php";
              }
              break;

            case "alter_book_title":
              if ($data) {
                include _LIB_LOC . "/action.alter_book_title.php";
              } else {
                include _LIB_LOC . "/page.alter_book_title.php";
              }
              break;

            case "bind_book":
              if ($data) {
                include _LIB_LOC . "/action.bind_book.php";
              } else {
                include _LIB_LOC . "/page.bind_book.php";
              }
              break;

            case "search" :
              include _LIB_LOC . "/form.lootbody.inc.php";
              break;

            case "setfreq" :
              include _LIB_LOC . "/page.setradiofreq.inc.php";
              break;

            case "sextant" :
              include _LIB_LOC . "/page.sextant.inc.php";
              break;

            case "speed" :
              if ($data) {
                include _LIB_LOC . "/action.adjustspeed.inc.php";
              } else {
                include _LIB_LOC . "/form.adjustspeed.inc.php";
              }
              break;

            case "store" :
              if ($data) {
                include _LIB_LOC . "/action.store.php";
              } else {
                include _LIB_LOC . "/page.store.inc.php";
              }
              break;

            case "ingest_all" :
              if ($data) {
                include _LIB_LOC . "/action.ingest_all.php";
              } else {
                include _LIB_LOC . "/form.ingest_all.php";
              }
              break;

            case "tossacoin" :
              include _LIB_LOC . "/action.tossacoin.inc.php";
              break;

            case "rolladie" :
              include _LIB_LOC . "/action.rolladie.php";
              break;

            case "travel" :
              include _LIB_LOC . "/action.travel.inc.php";
              break;

            case "turnaround" :
              include _LIB_LOC . "/action.turnaround.inc.php";
              break;

            case "undock" :
              include _LIB_LOC . "/action.undock.inc.php";
              break;

            case "use" :
              if ($data) {
                include _LIB_LOC . "/action.use.inc.php";
              } else {
                if ($_REQUEST['choice']) {
                  include _LIB_LOC . "/form.amount.use.inc.php";
                } else {
                  include _LIB_LOC . "/form.select.use.inc.php";
                }
              }
              break;

            case "usecoinpress" :
              include _LIB_LOC . "/page.usecoinpress.inc.php";
              break;

            case "useobject" :
              if ($_REQUEST['project']) {
                include _LIB_LOC . "/action.useobject.inc.php";
              } else {
                include _LIB_LOC . "/form.selectproject.useobject.inc.php";
              }
              break;

            case "useraw" :
              if ($_REQUEST['project']) {
                if ($data) {
                  include _LIB_LOC . "/action.useraw.inc.php";
                } else {
                  include _LIB_LOC . "/form.useraw.inc.php";
                }
              } else {
                include _LIB_LOC . "/form.selectproject.inc.php";
              }
              break;

            case "wear" :
            case "unwear" :
              include _LIB_LOC . "/action.wear_clothes.inc.php";
              break;

            case "writenote" :
              if ($data && !empty($_REQUEST['object_id'])) { // edit
                include _LIB_LOC . "/action.editnote.inc.php";
              } elseif ($data && empty($_REQUEST['object_id'])) { // create new
                include _LIB_LOC . "/action.writenote.inc.php";
              } else { // show edit form
                include _LIB_LOC . "/form.writenote.inc.php";
              }
              break;

            default :
              CError::throwRedirect("char", "<CANTR REPLACE NAME=error_page_unavailable PAGE=$page> (char: $character)");
          }
        }
    }
}

// JavaScript popup char description
$output = ob_get_contents();

$tag = new tag;
$tag->character = $character;
$tag->content = $output;
$tag->html = true;
$tag->language = $l;

$output = $tag->interpret();

ob_end_clean();

ob_start();

$StyleSheetsRef = array(
  "ce" => "cantrexp.css",
  "build" => "buildmenu.css",
  "votinglinks" => "admin.css",
  "manage_animals" => "admin.css",
  "notes_log" => "admin.css",
  "" => "frontpage.css",
  "adduser" => "frontpage.css",
  "afterapplication" => "frontpage.css",
  "intro" => "frontpage.css",
  "animals" => "animals.css",
  "animal_butcher" => "animals.css",
  "char.events" => "events.css",
  "char" => "events.css",
);

$allowResponsiveLayout = true;
if ($player) { // custom skin preferences for logged in users
  $playerOptions = PlayerSettings::getInstance($player);
  $allowResponsiveLayout = $playerOptions->get(PlayerSettings::RESPONSIVE_LAYOUT) == 0;

  $noteView = in_array($page, array("readnote", "writenote"));
  $noteClassic = $playerOptions->get(PlayerSettings::CSS_CLASSIC_NOTE) == 0;

  $forceClassic = $noteView && $noteClassic;

  $skin = new SkinHandler($player);
  if ($forceClassic) { // when classic css should be used because of mobile dev or viewing note
    $StyleSheets [] = SkinHandler::CLASSIC_CSS;
  } else {
    if ($skin->getSecondaryPath() && $playerOptions->get(PlayerSettings::CSS_EXTEND_BASE) == 1) {
      $StyleSheets[] = $skin->getSecondaryPath();
    }
    $StyleSheets [] = $skin->getMainPath();
  }
} else {
  $StyleSheets [] = SkinHandler::DEFAULT_CSS;
}

if (isset($StyleSheetsRef [$page])) {
  $StyleSheets [] = $StyleSheetsRef [$page];
}

$smartyMain->assign("allowResponsiveLayout", $allowResponsiveLayout);
$smartyMain->assign("StyleSheets", $StyleSheets);
$smartyMain->assign("PageContents", $output);
$smartyMain->assign("js_translations", json_encode(JsTranslations::getManager()->getTranslations()));
if (!isset($s)) {
  $smartyMain->assign('referrer', $referrer);
}

// performance measuring
list ($usec, $sec) = explode(" ", microtime());
$time = $usec + $sec % 10000000000 - $time;

$stm = $db->query("SELECT procname FROM servprocrunning");
$processes = $stm->fetchScalars();
$processNumber = count($processes);
$processes = Pipe::from($processes)->map(function($process) {
  return substr($process, 7);
})->implode(", ");

if ($processNumber) {
  $processes = "<br>$processNumber process(es) (" . $processes . ").";
}

$TimeStats = sprintf("<img src=\"/graphics/cantr/pictures/cog.png\" align=\"absmiddle\">%.3f seconds taken, %d queries executed (SQL took %.3f seconds). $processes",
  $time, $sqlcount, $sqltime, 100 * $sqltime / $time, $tagtime);
$smartyMain->assign("TimeStats", $TimeStats);

$smartyMain->assign("headerSubtitle", $env->getConfig()->subtitle());

ob_start();
$smartyMain->displayLang("index.tpl", $lang_abr);
$output = EncodeURIs(ob_get_contents());

$tag = new tag;
$tag->character = $character;
$tag->content = $output;
$tag->html = true;
$tag->language = $l;

$output = $tag->interpret();

ob_end_clean();
ob_start();

echo $output; //EncodeURIs ($output);

if (mt_rand(0, 1000) < 20) {
  $timing = new Timing($db);
  $timing->store(false);
}

// ------------------- error_bar ----------------------
function error_bar($error, $lang)
{
  $tag = new tag($error);
  $error = htmlentities($tag->interpret(), ENT_COMPAT | ENT_HTML401, 'utf-8');
  $error = urldecode($error);

  $error_smarty = new CantrSmarty;
  $error_smarty->assign("error_text", $error);
  $error_smarty->displayLang("template.error.tpl", $lang);
}

function show_title($title)
{
  echo '<div class="titlebar txt-title">';
  echo "$title";
  echo '</div>';
}

function updateLanguageCookie($languageId)
{
  if (array_key_exists($languageId, LanguageConstants::$LANGUAGE)) {
    setcookie("page_language", LanguageConstants::$LANGUAGE[$languageId]['lang_abr'], time() + 60 * 60 * 24 * 30 * 12, "/");
  }
}

function checkIsDBAvailable()
{
  // Testing if database is available
  $db = Db::get();
  try {
    $db->query("SELECT 1");
  } catch (PDOException $e) {
    echo "<table width=100% height=100%><tr><td align=center vAlign=center>" .
      "<img src=\"graphics/cantr/pictures/cantrbackuplogo.jpg\" border=0 /><br>" .
      "<b>Cantr II is not currently available. </b><br><br>

       The game and forums are backed up on a daily basis at 15:20 GMT. <br>
       This process takes approximately 25 minutes. Please try this site later.<br><br>

           In the mean time you may like to vote for Cantr on the following sites:<br>";
    switch (rand(0, 3)) {
      case 0:
        echo "
           <a href=\"http://www.oz-games200.com/in.php?gameid=33\" target=blank>Oz-games200.com</a>,
       <A HREF=\"http://games.plit.dk/cgi-bin/games.pl?_cmd=ShowGame&GameId=95\" TARGET=blank>Ultimate Game Directory</A>,
       <A HREF=\"http://www.rpggateway.com/cgi-bin/wyrm/search.cgi?query=cantr\" TARGET=blank>RPG Gateway</A>";
        break;
      case 1:
        echo "
       <A HREF=\"http://www.free-games.com.au/Detailed/929.html\" TARGET=blank>Free-games Australia</A>,
       <A HREF=\"http://www.sweetonlinegames.com/?a=vote&gid=50\" TARGET=blank>Sweet Online Games</A>,
       <A HREF=\"http://www.toprpgames.com/vote.php?idno=409\" TARGET=blank>Top RP Games</A>";
        break;
      case 2:
        echo "
       <A HREF=\"http://www.directoryofgames.com/main.php?view=topgames&action=vote&v_tgame=136\" TARGET=blank>Directory of Games</A>,
       <A HREF=\"http://omgn.com/topgames/vote.php?Game_ID=731\" TARGET=blank>Online Multiplayers Game Network</A>,
       <A HREF=\"http://www.mudmagic.com/rate.php?id=1416\" TARGET=blank>Mud Magic</A>";
        break;
      case 3:
        echo "
       <A HREF=\"http://www.gamesites200.com/gaming/vote.php?id=3969\" TARGET=blank>Gaming Top 200</A>,
       <A HREF=\"http://top50.onrpg.com/in.php?id=883\">MMORPG/MMOG Top 50 Games</A>";
        break;
    }
    echo
      "<br>or speak with other players in the #cantr channel on (irc.newnet.net)." .
      "</td></tr></table>";
    echo "\n</body>\n</html>";
    die ();
  }
}

?>
