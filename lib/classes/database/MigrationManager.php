<?php

class MigrationManager
{
  private $db;
  /** @var callable[] */
  private $migrations;

  public function __construct(Db $db)
  {
    $this->db = $db;

    // migration scripts should be idempotent to avoid problems when something breaks in the middle of migration
    // MySQL doesn't allow transactions for DDL
    $this->migrations = [
      2 => function(Db $db) {
        // do nothing, it's just an example how should a migration script look like
      },
      3 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if (!$this->columnExists($db, $env, "rawtypes", "taint_target_weight")) {
          $db->query("ALTER TABLE rawtypes ADD COLUMN taint_target_weight INT DEFAULT 0 NOT NULL");
        }
        $db->query("DROP FUNCTION IF EXISTS amount_to_taint");
        /** when changing this function, remember to change method `DeteriorationManager::amountToTaint` accordingly */
        $db->query("
        CREATE FUNCTION amount_to_taint(weight FLOAT, target_weight FLOAT,  max_taint_percent FLOAT)
          RETURNS FLOAT DETERMINISTIC
        BEGIN
          RETURN LEAST(SQRT(weight / (target_weight / 35)) / 2000, max_taint_percent) * weight;
        END");
        $db->query("REPLACE INTO global_config VALUES ('universal_taint_enabled', 0)");
        $this->addTexts($db, [
          "event_379" => "Project cancellation resulted in #RECOVER#. #LOST# has been lost because of taint.",
        ]);
      },
      4 => function(Db $db) {
        $db->query("ALTER TABLE `ingame_stats` CHANGE `subtype` `subtype` VARCHAR( 256 )
            CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
      },
      5 => function(Db $db) {
        $this->addTexts($db, [
          "error_cannot_loot_living" => "You cannot loot a living character!",
        ]);
      },
      6 => function(Db $db) {
        $db->query("REPLACE INTO categories (name, full_description) VALUES ('terrain', 'Terrain types of central areas')");
        $db->query("REPLACE INTO obj_properties SELECT id, 'AccessibleRoadTypes',
                       '[\"path\", \"sand_road\", \"impassable\", \"paved_road\", \"highway\", \"expressway\", \"railroad\"]'
                       FROM objecttypes WHERE category = 'terrain'");
        $this->addTexts($db, [
          "event_377" => "You arrive in #BUILDING# of #LOCATION#.",
          "event_378" => "You see #VEHICLE# arriving in #BUILDING# of #DESTINATION#, coming from <CANTR REPLACE NAME=#ROAD#> to #ORIGIN#.",
          "error_only_near_sea_or_buildings" => "You can only build this when you are at a location that borders a sea or in buildings: #TYPES#",
          "error_only_near_lake_or_buildings" => "You can only build this when you are at a location that borders a lake or in buildings: #TYPES#",
          "error_only_near_water_or_buildings" => "You can only build this when you are at a location that borders water or in buildings: #TYPES#",
          "error_only_outside_or_buildings" => "You can only build this when you are outside or in buildings: #TYPES#",
          "error_nothing_to_undock_from" => "There is nothing to undock from",
          "error_object_preventing_travel" => "You cannot start travel, because existence of <CANTR OBJNAME ID=#OBJECT_ID#> prevents it."
        ]);
      },
      7 => function(Db $db) {
        $this->addTexts($db, [
          "sailing_turns" => "turns (set 0 to not stop at all)",
          "sailing_stop_after" => "Stop after",
          "set_ship_course_button" => "Set ship course",
        ]);
      },
      8 => function(Db $db) {
        $this->addTexts($db, [
          "project_problem_project_not_here" => "Project is not here",
          "project_problem_not_on_sea_or_lake" => "Project can progress only on sea or lake",
          "project_problem_not_on_moving_boat" => "Project can progress only on a moving boat",
          "project_problem_not_on_floating_boat" => "Project can progress only on a floating boat",
          "project_problem_not_on_docked_boat" => "Project can progress only on a docked boat",
          "project_problem_not_in_parked_land_vehicle" => "Project can progress only in a parked vehicle",
          "project_problem_not_inside_a_building" => "Project can progress only inside",
          "project_problem_not_outside" => "Project can progress only outside",
          "project_problem_not_travelling_in_land_vehicle" => "Project can progress only when travelling in land vehicle",
          "project_problem_not_on_land" => "Project can progress only on land",
          "project_problem_not_on_sea" => "Project can progress only at sea",
          "project_problem_not_on_lake" => "Project can progress only on lake",
          "project_problem_sign_not_here" => "The sign is too far away",
          "project_problem_lock_not_unlocked" => "It's not possible to disassemble a lock while it is locked",
          "project_problem_not_initiator" => "Only initiator can work on the project",
          "project_problem_target_not_here" => "Target of the project is not here",
          "project_problem_target_not_empty" => "Target of the project is not empty",
          "project_problem_missing_tools" => "You do not have the right tools",
          "project_problem_missing_raws" => "Raw material requirements are missing",
          "project_problem_missing_objects" => "Object requirements are missing",
          "project_info_problems" => "Problems preventing progress:",
        ]);
      },
      9 => function(Db $db) {
        $this->addTexts($db, [
          "ship_is_docking" => "(docking to location)",
        ]);
      },
      10 => function(Db $db) {
        $db->query("REPLACE INTO access_types (id, description) VALUES (53, 'Allowed to see admin data')");
      },
      11 => function(Db $db) {
        $this->addTexts($db, [
          "error_cannot_build_travelling" => "Cannot start a project when travelling",
        ]);
      },
      12 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "sailing", "turns")) {
          $db->query("ALTER TABLE sailing CHANGE `turns` `sailing_stop_timestamp` int DEFAULT 0 NOT NULL");
          $db->query("UPDATE sailing SET sailing_stop_timestamp =
              (((SELECT day FROM turn) * 8 + (SELECT hour FROM turn)) * 36 * 60) + sailing_stop_timestamp * 36 * 60
            WHERE sailing_stop_timestamp != 0");
        }
      },
      13 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if (!$this->columnExists($db, $env, "rawtypes", "agricultural")) {
          $db->query("ALTER TABLE rawtypes ADD COLUMN agricultural BOOLEAN DEFAULT false NOT NULL");
        }
        $this->addTexts($db, [
          "harvest_efficiency_normal" => "Normal harvest",
          "harvest_efficiency_too_cold" => "No harvest because it's too cold",
          "harvest_efficiency_good" => "Good harvest due to <CANTR REPLACE NAME=#VALUE_ABOVE_AVERAGE#>",
          "harvest_efficiency_perfect" => "Perfect harvest due to sun and rain",
          "harvest_efficiency_poor" => "Poor harvest due to <CANTR REPLACE NAME=#VALUE_BELOW_AVERAGE#>",
          "harvest_efficiency_no" => "No harvest due to not enough sun and not enough rain",
          "harvest_above_average_insolation" => "lots of sun",
          "harvest_above_average_humidity" => "lots of rain",
          "harvest_below_average_insolation" => "not enough sun",
          "harvest_below_average_humidity" => "not enough rain",
        ]);
      },
      14 => function(Db $db) {
        $db->query("ALTER TABLE objects MODIFY deterioration FLOAT DEFAULT 0 NOT NULL");
        $db->query("ALTER TABLE connections MODIFY deterioration FLOAT DEFAULT 0 NOT NULL");
        $db->query("ALTER TABLE projects MODIFY turnsleft FLOAT NOT NULL");
        $db->query("ALTER TABLE travels MODIFY travleft FLOAT NOT NULL");
        $db->query("UPDATE travels SET travleft = travleft / 8, travneeded = travneeded / 8");
        // update furniture and projects for new resting values
        $db->query("UPDATE obj_properties SET details = '{\"days\": 0.25}' WHERE objecttype_id = 7");
      },
      15 => function(Db $db) {
        $db->query("ALTER TABLE projects MODIFY turnsneeded FLOAT NOT NULL");
      },
      16 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "sessions", "mark")) {
          $db->query("ALTER TABLE sessions DROP COLUMN mark");
        }
        if ($this->columnExists($db, $env, "sessions", "lastpage")) {
          $db->query("ALTER TABLE sessions DROP COLUMN lastpage");
        }
        if ($this->columnExists($db, $env, "sessions", "passthru")) {
          $db->query("ALTER TABLE sessions DROP COLUMN passthru");
        }
      },
      17 => function(Db $db) {
        $db->query("REPLACE INTO access_types (id, description) VALUES (54, 'Allowed to avoid redirect to URL set in the environment configuration')");
      },
      18 => function(Db $db) {
        $db->query("DROP TABLE IF EXISTS categories");
        $db->query("DROP TABLE IF EXISTS dbinfo");
        $db->query("DROP TABLE IF EXISTS areas");
      },
      19 => function(Db $db) {
        $this->addTexts($db, [
          "page_global_exception_handler" => "<h3>Something was wrong...</h3><a href='/'>Back to homepage</a>",
        ]);
      },
      20 => function(Db $db) {
        $this->addTexts($db, [
          "project_picking_lock_location" => "Picking lock no. #LOCKID# at <CANTR LOCNAME ID=#TARGET#>",
          "project_picking_lock_object" => "Picking lock no. #LOCKID# at <CANTR REPLACE NAME=#TARGET#>",
        ]);
      },
      21 => function(Db $db) {
        $this->addTexts($db, [
          "email_character_death_info" => "A character that died cannot be recovered.
You can, however, always start a new character, with a new identity, new goals, and a new game to 
play. If you have questions, send a note to support@cantr.net and someone will help you out.

The last events for this character were:",
        ]);
      },
      22 => function(Db $db) {
        $this->addTexts($db, [
          "message_player_accepted" => "Your account is now verified and you can create new characters. Have fun!",
          "error_create_character_account_pending" => "You cannot create new characters until your account gets verified",
          "register_characters_explanation" => "Your first character is in the small but active world, where you are free to ask questions from the perspective of a player. Soon, you will be able to create more characters on the biggest world of Cantr, where each character has to act independently. The option to create more characters will appear at the bottom of your character list.",
          "register_first_character_title" => "Your first character"
        ]);
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "newplayers", "firstname")) {
          $db->query("ALTER TABLE newplayers
          DROP INDEX username_UNIQUE,
          DROP COLUMN firstname, DROP COLUMN lastname, DROP COLUMN email, DROP COLUMN age,
          DROP COLUMN country, DROP COLUMN language, DROP COLUMN password, DROP COLUMN register,
          DROP COLUMN approved, DROP COLUMN username, DROP COLUMN referrer");
        }
        $db->query("ALTER TABLE `players` CHANGE `notes` `notes` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
      },
      23 => function(Db $db) {
        $columnInfo = $db->query("SHOW FIELDS FROM newplayers WHERE field = 'refplayer'")->fetchObject();
        if ($columnInfo->Type !== "tinytext") {
          $db->query("ALTER TABLE newplayers MODIFY refplayer TINYTEXT NULL");
          $db->query("ALTER TABLE newplayers DROP INDEX id");
          $db->query("ALTER TABLE newplayers ADD PRIMARY KEY (id)");
        }
      },
      24 => function(Db $db) {
        $this->addTexts($db, [
          "mail_new_player_reminder" => "Greetings,

A couple of days ago you registered an account for the game Cantr II. We sent you an activation email, although we have noticed that you still haven't entered the game.
If you want to give it a try, please <a href=\"#LOGIN_LINK#\">follow this link</a>.
If you don't want to play the game, sorry to see you go.

Jos Elkink

(this is an automatically sent message)",
        ]);
        $db->query("UPDATE texts SET name = 'mail_new_player_reminder_title' WHERE name = 'mail_intro_idleout_warn_title'");
      },
      25 => function(Db $db) {
        $this->addTexts($db, [
          "message_new_player" => "Hello,<br>
Your first character lives in a small world with other players, where you are free to play and ask questions about the game.<br>
You will be able to create more characters when your account is verified.",
        ]);
      },
      26 => function(Db $db) {
        $this->addTexts($db, [
          "project_copying_key" => "Copy key no. #KEYID#",
        ]);
      },
      27 => function(Db $db) {
        $this->addTexts($db, [
          "project_destroying_lock_location" => "Destroying lock no. #LOCKID# at <CANTR LOCNAME ID=#TARGET#>",
          "project_destroying_lock_object" => "Destroying lock no. #LOCKID# at <CANTR REPLACE NAME=#TARGET#>",
        ]);
      },
      28 => function(Db $db) {
        $this->addTexts($db, [
          "create_new_character" => "Create new character",
          "player_nav_chat" => "Chat",
          "player_nav_forum" => "Forum",
          "player_nav_wiki" => "Wiki",
          "player_nav_contact" => "Contact Us",
          "player_nav_settings" => "Settings",
          "player_nav_logout" => "Logout",
        ]);
        $db->query("UPDATE texts SET name = 'title_settings_unsubscribe' WHERE name = 'alt_unsubscribe'");
      },
      29 => function(Db $db) {
        $db->query("UPDATE sessions SET lasttime = NOW()");
        $db->query("ALTER TABLE sessions MODIFY lasttime DATETIME NOT NULL");
      },
      30 => function(Db $db) {
        $this->addTexts($db, [
          "error_location_cannot_be_repaired" => "This location cannot be repaired",
          "title_repair_location" => "Repair location",
          "repair_location_text" => "Repair #NAME#.<br>
If you don't have enough raws you can make a partial repair equal to the amount of the least abundant resource on the ground",
          "repair_location_recursively" => "Repair also all locations inside this location",
          "cannot_repair_recursively_raws_missing" => "You could automatically repair all locations inside this location if you obtain all the raws listed above",
          "repair_location_raw" => "Resource",
          "repair_location_singular_cost" => "Only this location",
          "repair_location_recursively_cost" => "This location and all inner rooms",
          "repair_location_raws_on_ground" => "Available on ground",
          "location_repair_button" => "Repair",
          "page_location_repair" => "Repair this location",
          "event_380" => "You repair <CANTR LOCNAME ID=#LOCATION_ID#>.",
          "event_381" => "You see <CANTR CHARNAME ID=#ACTOR#> repair <CANTR LOCNAME ID=#LOCATION_ID#>.",
          "event_382" => "You repair <CANTR LOCNAME ID=#LOCATION_ID#> and its inner locations.",
          "event_383" => "You see <CANTR CHARNAME ID=#ACTOR#> repair <CANTR LOCNAME ID=#LOCATION_ID#> and its inner locations.",
        ]);
      },
      31 => function(Db $db) {
        $this->addTexts($db, [
          "page_desc_docking" => "This vessel is currently docking (about #DOCKING# hour(s) left)",
        ]);
      },
      32 => function(Db $db) {
        $db->query("UPDATE access_types SET description = 'Allowed to alter passwords of staff members' WHERE id = 2");
      },
      33 => function(Db $db) {
        $this->addTexts($db, [
          "statistics_public_title" => "STATISTICAL OVERVIEW CANTR",
          "statistics_public_language_group" => "Language group statistics",
          "statistics_public_time_trends" => "Time trends",
        ]);
      },
      34 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "languages", "encoding")) {
          $db->query("ALTER TABLE languages DROP COLUMN encoding, DROP COLUMN encoding_mysql, DROP COLUMN newbie_island");
        }
      },
      35 => function(Db $db) {
        $db->query("INSERT IGNORE INTO languages (id, name, spawning_allowed, use_density_spawning, original_name, abbreviation, paypal_lc)
          VALUES (20, 'japanese', 0, 0, '日本語', 'jp', 'JP')");
        $this->addTexts($db, [
          "lang_japanese" => "Japanese",
        ]);
      },
      36 => function(Db $db) {
        $db->query("ALTER TABLE animal_types MODIFY id SMALLINT UNSIGNED AUTO_INCREMENT");
        $db->query("DROP TABLE IF EXISTS clothes"); // not used since 2012, duplicates objecttypes
      },
      37 => function(Db $db) {
        $db->query("ALTER TABLE advertisement MODIFY id SMALLINT AUTO_INCREMENT, ALTER COLUMN date DROP DEFAULT");
        $db->query("DROP TABLE IF EXISTS cook, cookmult");
        $db->query("ALTER TABLE `ips` CHANGE `lasttime` `lasttime` DATETIME NULL DEFAULT NULL");
        $db->query("ALTER TABLE `pcstatistics` CHANGE `actiondate` `actiondate` DATETIME NULL DEFAULT NULL");
        $db->query("UPDATE texts SET updated = '2020-09-25' WHERE DATE_FORMAT(updated, '%Y-%m-%d') = '0000-00-00'");
        $db->query("ALTER TABLE `texts` CHANGE `updated` `updated` DATE NOT NULL");
        $db->query("ALTER TABLE `uls_daystats` CHANGE `day` `day` DATE NOT NULL");
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "uls_lastreq", "reset")) {
          $db->query("ALTER TABLE `uls_lastreq` DROP COLUMN reset");
        }
      },
      38 => function(Db $db) {
        $this->addTexts($db, [
          "form_create_on_genesis" => "Create on Genesis",
        ]);
        $this->addTexts($db, [
          "form_world" => "World",
        ]);
      },
      39 => function(Db $db) {
        $db->query("ALTER TABLE statistics MODIFY type VARCHAR(30) CHARSET LATIN1 NULL");
      },
      40 => function(Db $db) {
        $db->query("ALTER TABLE players MODIFY id MEDIUMINT UNSIGNED AUTO_INCREMENT");
        $db->query("ALTER TABLE messages MODIFY id INT AUTO_INCREMENT, ADD INDEX id (id), ADD CONSTRAINT id PRIMARY KEY (id, language)");
        $db->query("DROP TABLE IF EXISTS ids");
      },
      41 => function(Db $db) {
        $db->query("DROP FUNCTION IF EXISTS amount_to_taint");
        $db->query("CREATE FUNCTION amount_to_taint(weight FLOAT, target_weight FLOAT,  max_taint_percent FLOAT)
          RETURNS FLOAT DETERMINISTIC
        BEGIN
          IF target_weight IS NULL OR target_weight = 0 THEN
            RETURN 0;
          ELSE
            RETURN LEAST(SQRT(weight / (target_weight / 35)) / 2000, max_taint_percent) * weight;
          END IF;
        END");
      },
      42 => function(Db $db) {
        $this->addTexts($db, [
          "error_not_dragging" => "You are not dragging anything",
        ]);
      },
      43 => function(Db $db) {
        $db->query("INSERT IGNORE INTO global_config (`key`, value)
          VALUES ('project_progress_ratio', 1), ('travel_progress_ratio', 1),
                 ('sailing_progress_ratio', 1), ('deterioration_ratio', 1)");
      },
      44 => function(Db $db) {
        $db->query("INSERT IGNORE INTO global_config (`key`, value) VALUES ('max_characters_per_player', 15)");
        $db->query("UPDATE texts SET content = REPLACE(content, '15', '#MAX_CHARACTERS#') WHERE name = 'error_max_characters'");
      },
      45 => function(Db $db) {
        $this->addTexts($db, [
          "error_need_access_to_air_or_nest" => "Tou need to be in a location with access to the open air or with a nest",
          "error_messenger_bird_invalid_home" => "You are trying to dispatch the bird to the home not known to it",
          "error_bird_nest_must_be_fixed" => "Bird nest must be a fixed object in the place that cannot move",
          "error_invalid_bird_home" => "This cannot be a bird's home",
          "error_animal_carrying_objects" => "Animal should not carry any objects",
          "js_form_dispatch_messenger" => "Dispatch",
          "js_set_messenger_home_text_1" => "Set",
          "js_set_messenger_home_text_2" => "as a new home",
          "js_form_set_messenger_home" => "Set new home",
          "js_select_home_to_dispatch_text" => "Dispatch the messenger to home",
          "js_turn_back_from_messenger_text" => "Turn back from being a messenger",
          "event_384" => "You set home no. #WHICH_HOME# of <CANTR OBJNAME ID=#BIRD_ID#> to <CANTR OBJNAME ID=#HOME_NEST_ID#>.",
          "event_385" => "You see <CANTR CHARNAME ID=#ACTOR#> home no. #WHICH_HOME# of <CANTR OBJNAME ID=#BIRD_ID#> to <CANTR OBJNAME ID=#HOME_NEST_ID#>.",
          "event_386" => "You dispatch <CANTR OBJNAME ID=#BIRD_ID#> in direction <CANTR REPLACE NAME=#DIRECTION#>.",
          "event_387" => "You see <CANTR CHARNAME ID=#ACTOR#> dispatch <CANTR OBJNAME ID=#BIRD_ID#> in direction <CANTR REPLACE NAME=#DIRECTION#>.",
          "event_388" => "You see <CANTR OBJNAME ID=#BIRD_ID#> fly from <CANTR REPLACE NAME=#DIRECTION#> and land in <CANTR OBJNAME ID=#HOME_NEST_ID#>.",
          "event_389" => "You see <CANTR OBJNAME ID=#BIRD_ID#> fly from <CANTR REPLACE NAME=#DIRECTION#> into <CANTR LOCNAME ID=#OUTERMOST_BUILDING_ID#>.",
          "event_390" => "You see <CANTR OBJNAME ID=#BIRD_ID#> fly from <CANTR REPLACE NAME=#DIRECTION#> and land in the central area of <CANTR LOCNAME ID=#LOCATION#>.",
          "event_391" => "You see a flying <CANTR OBJNAME ID=#BIRD_ID#> falls dead on the ground.",
        ]);
      },
      46 => function(Db $db) {
        $db->query("ALTER TABLE `corners` ADD PRIMARY KEY (`id`)");
        $db->query("UPDATE corners SET changedir = 0 WHERE id IN (2694, 307, 2683, 2699)");
        $db->query("UPDATE corners SET next = 1656 WHERE id = 1749");
        $db->query("DELETE FROM corners WHERE id = 1750");
      },
      47 => function(Db $db) {
        $env = Request::getInstance()->getEnvironment();
        if ($this->columnExists($db, $env, "messenger_birds", "max_speed")) {
          $db->query("ALTER TABLE messenger_birds DROP COLUMN max_speed");
        }
      }
    ];
  }

  private function columnExists(Db $db, Environment $env, $table, $column)
  {
    $stm = $db->prepare("SELECT COUNT(*) FROM `information_schema`.`columns`
          WHERE table_schema = :dbName AND table_name = :table and column_name = :column");

    $stm->bindStr("dbName", $env->getConfig()->dbName());
    $stm->bindStr("table", $table);
    $stm->bindStr("column", $column);
    return $stm->executeScalar() > 0;
  }

  public function performMigration()
  {
    $currentVersion = $this->getCurrentDbVersion();
    if (!$currentVersion) {
      throw new IllegalStateException("No `db_version` table found!");
    }

    foreach ($this->migrations as $version => $migrationFunction) {
      try {
        if ($currentVersion < $version) {
          $migrationFunction($this->db);
          $this->setDbVersion($version);
        }
      } catch (Exception $e) {
        throw new IllegalStateException("Unexpected exception when trying to migrate to db version $version", 0, $e);
      }
    }
  }

  public function getCurrentDbVersion()
  {
    $stm = $this->db->prepare("SELECT `number` FROM db_version");
    return intval($stm->executeScalar());
  }

  private function setDbVersion($version)
  {
    $stm = $this->db->prepare("UPDATE db_version SET `number` = :number, `date` = NOW()");
    $stm->bindInt("number", $version);
    $stm->execute();
  }

  public function getVersionOfLastMigration()
  {
    return max(array_keys($this->migrations));
  }

  private function addTexts(Db $db, $contentByName)
  {
    $stm = $db->prepare("REPLACE INTO texts (type, language, name, content, grammar, translator, updated)
      VALUES (:type, :language, :name, :content, '', :translator, NOW())");
    foreach ($contentByName as $tagName => $tagContent) {
      $stm->bindInt("type", 1);
      $stm->bindInt("language", LanguageConstants::ENGLISH);
      $stm->bindStr("name", $tagName);
      $stm->bindStr("content", $tagContent);
      $stm->bindStr("translator", "Migration Tool");
      $stm->execute();
    }
  }
}
