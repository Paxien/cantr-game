<?php

/* SYSTEM TIME */
date_default_timezone_set("Europe/London");

/*
if ($_SERVER['DOCUMENT_ROOT']) { // TYMCZASOWE!!! TODO!!!
  define("_ROOT_LOC", substr($_SERVER['DOCUMENT_ROOT'], 0, strrpos($_SERVER['DOCUMENT_ROOT'], "/")));
}
*/
/* ENVIRONMENT */

define ("_PATH_DELIMITER", '/');

define("_ROOT_LOC", substr(__DIR__, 0, strrpos(__DIR__, _PATH_DELIMITER)));
define("_LIB_LOC", _ROOT_LOC . _PATH_DELIMITER . "lib");

require_once(_ROOT_LOC . '/vendor/autoload.php');

/* IMAGES */
define ("_IMAGES", "/graphics/cantr/pictures");
define ("_IMAGES_OBJECTS", "/graphics/cantr/objects/");
define ("_IMAGES_OBJECTS_REL","/graphics/cantr/objects/");

/* OBJECTS */
define ("_OBJ_SETTING_NORMAL", 1);
define ("_OBJ_SETTING_QUANTITY", 2);
define ("_OBJ_CATEGORY_HARBOURS", 4);
define ("_OBJ_CATEGORY_INSTRUMENTS", 22);
define ("_OBJ_CATEGORY_CLOTHES", 26); // see ObjectConstants
define ("_OBJ_CATEGORY_SHIPS", 34);

define ("_OBJ_TYPE_INNERLOCK", 13 );
define ("_OBJ_TYPE_CONTAINERLOCK", _OBJ_TYPE_INNERLOCK );
define ("_OBJ_TYPE_NOTICEBOARD", 100 );

/* COIN PRESS */
define ("_MAX_AMOUNT_COINS", "100");
define ("_MAX_PARTICIPANTS", "2");
define ("_AMOUNT_COINS_PER_DAY", "20");
define ("_GRAMS_PER_COIN", "10");

/* MISC */
define ("_TURNS", "8");
define ("_EVTEXP", "5"); // Number of times an event has to be seen before it expires from the standard view.
define ("_EXPFTHBCK", 6); // Number of turns (current turn excluded) that should be visible when using the 'further back' button.
define ("_EXPTIMING", "30"); // Number of days after which timing of page loads are removed.
define ("_MAX_NATURAL_HEALING","2"); //This is the maximum value of natural healing -a day-
define ("_BASE_HEALING","0.25"); //This is the part of the Max Natural Healing given without furniture (should be a number between 0 and 1)
define ("_MAX_CONTAINER_VISIBLE", 6);
define ("_RECAPTCHA_SITE_KEY", "6LcpgJkUAAAAAKjEhQy5RQz5dha9Xah9rU3_atvr");
define ("_RECAPTCHA_SECRET_KEY", "6LcpgJkUAAAAADrYGnImxdlFZuS_qdnQSQS5aPxq");

/* EVENTS */
define ("_EVENT_STORE_OTHERS", "42");
define ("_EVENT_STORE_SELF", "43");

define ("_EVENT_RADIO_TRANSMIT_ACTOR", "156");

define ("_EVENT_EDITCONTAINERNOTE_SELF", "258");
define ("_EVENT_EDITCONTAINERNOTE_OTHERS", "259");

define ("_EVENT_WINDOW_OPEN_SELF", "274");
define ("_EVENT_WINDOW_OPEN_OTHERS", "275");
define ("_EVENT_WINDOW_CLOSE_SELF", "276");
define ("_EVENT_WINDOW_CLOSE_OTHERS", "277");

define ("_EVENT_CHANGE_BUILDING_DESCRIPTION_SELF", "278");
define ("_EVENT_CHANGE_BUILDING_DESCRIPTION_OTHER", "279");
define ("_EVENT_START_CHANGE_BUILDING_DESCRIPTION_SELF", "280");
define ("_EVENT_START_CHANGE_BUILDING_DESCRIPTION_OTHER", "281");


/* CHARACTER */
define ("_CHAR_PENDING", "0");
define ("_CHAR_ACTIVE", "1");
define ("_CHAR_DECEASED", "2");
define ("_CHAR_BEING_BURIED", "3");
define ("_CHAR_BURIED", "4");
define ("_CHAR_DEATH_VIOLENCE", "1");
define ("_CHAR_DEATH_PD", "2");
define ("_CHAR_DEATH_UNSUB", "3");
define ("_CHAR_DEATH_ANIMAL", "4");
define ("_CHAR_DEATH_EXPIRED", "5");
define ("_CHAR_DEATH_STARVED", "6");

define ("_PLAYER_PENDING", "0");  /* Obsolete - here for completeness sake */
define ("_PLAYER_APPROVED", "1");
define ("_PLAYER_ACTIVE", "2");   /* Reactivated accounts get this status, new ones get 1/approved */
define ("_PLAYER_LOCKED", "3");   /* Thus assumption can be and is made that < LOCKED is active; >= LOCKED is not */
define ("_PLAYER_REMOVED", "4");
define ("_PLAYER_UNSUBSCRIBED", "5");
define ("_PLAYER_IDLEDOUT", "6");
define ("_PLAYER_REFUSED", "7");

define ("_PLAYER_MAX_CHARS", "15");
define ("_DEAD_CLOSE_SLOT_DAYS", "40"); /* Char slot closed for this number of days */
define ("_DEAD_CLOSE_SLOT_AGE", "100"); /* max age of char to apply slot closing */

/* VISIBILITY OF CHARACTER STATE ON PEOPLE/CHARACTER PAGE */
define ("_STATE_HUNGER_1", 5000); // number = hunger (10000 = starved)
define ("_STATE_HUNGER_2", 7500);
define ("_STATE_HUNGER_3", 9000);

define ("_STATE_WOUNDS_1", 9500); // number = health (10000 = healthy)
define ("_STATE_WOUNDS_2", 7500);
define ("_STATE_WOUNDS_3", 5000);

define ("_PEOPLE_WEIGHT", 60000); // see CharacterConstants::BODY_WEIGHT
define ("_PEOPLE_MAXWEIGHT", 15000); // see CharacterConstants::INVENTORY_WEIGHT_MAX

define ("_PEOPLE_NEAR", "5"); /* distance that people are considered near on a road */

define ("_LIGHTHOUSE_NEAR", "200"); /* distance that a lighthouse is considered near to a sailing vessel */
define ("_LIGHTHOUSE_NEAR_WITH_NAME", "60"); /* distance that a lighthouse is considered near enough to know the name */

define ("_SIGNALFIRE_RANGE", 60); /* distance that a signalfire is considered visible */
define ("_SIGNALFIRE_LAND", _SIGNALFIRE_RANGE);
define ("_SIGNALFIRE_SEA", _SIGNALFIRE_RANGE);

define ("_WALKING_WEIGHT_DELAY", "5000"); /* speed will be deduced by weight / delay */

define ("_WALKING_SPEED", "10");
define ("_LOW_FUEL_WARN_LEVEL","20");  // in %

define ("_EATPERDAY", "100");
define ("_EAT_PERCENT_BACK", "5");
define ("_EAT_PERCENT_BACK_HEALING", "2");
define ("_EAT_PERCENT_BACK_TIREDNESS", "2");
define ("_EAT_PERCENT_DRUNKENNESS", "20");
define ("_STOMACH_CAPACITY", "4000");//grams // see EatingConstants::STOMACH_CAPACITY
define ("_PASSOUT_LIMIT", "7500");//For drunkenness

define ("_HIT_PERCENT_DOWN", "4");

define ("_MAX_ROOMS", "20");

define ("_ANIMAL_DEV_ATTACK", ".1");
define ("_ANIMAL_DEV_MATE", ".003");
define ("_ANIMAL_DEV_TRAVEL", ".1");
define ("_ANIMAL_DEV_ATTACK_NUM", ".003");
define ("_ANIMAL_POPULATION_BOOST_LEVEL","4");
define ("_MAX_ANIMAL_POPULATION", "650000");

define ("_VIOLENCE_HIT_CHANCE", "80");
define ("_SHIELD_BLOCK_CHANCE", "70");
define ("_VIOLENCE_DEV", ".1");

// character limitations
define ("_LIMITATION_MAX_KNOCK", 5);

// wheelbarrows increase dragging strenth - 1 is 300 grams
define ("_WHEELBARROW_DRAGGING_EFFECT","60"); // see DraggingConstants::WHEELBARROW_DRAGGING_EFFECT
define ("_IMPROVED_WHEELBARROW_DRAGGING_EFFECT","100"); // see DraggingConstants::IMPROVED_WHEELBARROW_DRAGGING_EFFECT

// GSS = Gen / Skill / State
define ("_GSS_FARMING", "1");
define ("_GSS_FIGHTING", "2");
define ("_GSS_BUILDING", "3");
define ("_GSS_MANUFACTURING", "4");
define ("_GSS_RESISTANCE", "5");
define ("_GSS_HUNGER", "6");
define ("_GSS_THIRST", "7");
define ("_GSS_WALKING", "8");
define ("_GSS_HUNTING", "9");
define ("_GSS_TIREDNESS", "10");
define ("_GSS_FORESTING", "11");
define ("_GSS_DRUNKENNESS", "12");
define ("_GSS_STRENGTH", "13"); // now purely referring to physical strength / muscle power
define ("_GSS_HEALTH", "14"); // health as in not wounded - not directly related to diseases
define ("_GSS_HYGIENE", "15");
define ("_SCALESIZE_GSS", "10000");

$statenames[1] = "Farming";
$statenames[2] = "Fighting";
$statenames[3] = "Building";
$statenames[4] = "Manufacturing";
$statenames[5] = "Resistance";
$statenames[6] = "Hunger";
$statenames[7] = "Thirst";
$statenames[8] = "Walking";
$statenames[9] = "Hunting";
$statenames[10] = "Tiredness";
$statenames[11] = "Foresting";
$statenames[12] = "Drunkenness";
$statenames[13] = "Physical strength";
$statenames[14] = "Health";
$statenames[15] = "Hygiene";

define ("_NROFLANGS", "19");

$langcode[1] = "English";
$langcode[2] = "Dutch";
$langcode[3] = "French";
$langcode[4] = "German";
$langcode[5] = "Spanish";
$langcode[6] = "Russian";
$langcode[7] = "Swedish";
$langcode[8] = "Esperanto";
$langcode[9] = "Polish";
$langcode[10] = "Latin";
$langcode[11] = "Arabic";
$langcode[12] = "Turkish";
$langcode[13] = "Portuguese";
$langcode[14] = "Lithuanian";
$langcode[15] = "Chinese";
$langcode[16] = "Finnish";
$langcode[17] = "Lojban";
$langcode[18] = "Italian";
$langcode[19] = "Bulgarian";
$langcode[20] = "Japanese";

define ("_TIREDNESS_PER_DRAGGING","1500");

//cantr_session_id md5
define ("_SESSION_COOKIE_NAME", "40d0228e409c8b711909680cba94881c" );
define ("_SESSION_EXPIRE_MINUTES", "1440");
define ("_SESSION_ERROR_LOGIN", "1");
define ("_SESSION_ERROR_LOCKED", "2");
define ("_SESSION_ERROR_EXPIRED", "3");
define ("_SESSION_ERROR_WRONG_CHAR", "4");

define ("_CREDIT_PER_MINUTE", "5");

/* ASSIGNMENTS */
define("_COUNCIL_GAB", "1");
define("_COUNCIL_PROGD", "8");
define("_COUNCIL_RD", "5");
define("_COUNCIL_PD", "6");
define("_COUNCIL_LD", "9");
define("_COUNCIL_ASD", "10");
define("_COUNCIL_PR", "11");

define("_ASSIGN_HIDDEN_MEMBER", "0");
define("_ASSIGN_CHAIR", "1");
define("_ASSIGN_SENIOR_MEMBER", "2");
define("_ASSIGN_MEMBER", "3");
define("_ASSIGN_SPECIAL_MEMBER", "4");
define("_ASSIGN_ASPIRANT_MEMBER", "5");
define("_ASSIGN_ON_LEAVE", "6");

/* EMAILS */
$GLOBALS['emailResources'] = "resources@cantr.org";
$GLOBALS['emailPlayers'] = "playersdepartment@cantr.org";
$GLOBALS['emailProgramming'] = "programming@cantr.org";
$GLOBALS['emailCommunications'] = "communications@cantr.org";
$GLOBALS['emailSupport'] = "support@cantr.org";
$GLOBALS['emailFinances'] = "finances@cantr.org";
$GLOBALS['emailGAC'] = "gac@cantr.org";
$GLOBALS['emailGAB'] = "gab@cantr.org";
$GLOBALS['emailPersonnel'] = "humanresources@cantr.org";
$GLOBALS['emailGMS'] = "owner@cantr.org";
$GLOBALS['emailGameMaster'] = "owner@cantr.org";
$GLOBALS['emailMarketing'] = "marketing@cantr.org";
$GLOBALS['emailSender'] = "noreply@cantr.net";

/* CLOTHES */
define( '_CLOTH_CATEGORY_RINGS', 48 );
define( '_MAX_WEARING_RINGS', 10 );


/* EXIT SURVEY */
define('_EXIT_SURVEY_S_ID', 1);

$terms_of_use_versions[0] = "None";
$terms_of_use_versions[1] = "1.0";

require_once(_LIB_LOC . "/header.functions.inc.php");

$env = Request::getInstance()->getEnvironment();
ini_set("error_log", $env->absoluteOrRelativeToRootPath($env->getConfig()->errorLogFilePath()));
if ($env->getConfig()->environment() === "test") {
  ini_set('display_errors', 1);
}
define("_ENV", $env->getConfig()->environment());
