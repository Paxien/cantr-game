<?php

require_once(_LIB_LOC . "/func.grammar.inc.php");

class tag
{
  var $basetags;

  var $content;
  var $html;
  var $character;
  var $language;
  var $charname_cache;
  var $admin = false;
  var $cache_array;
  var $times = 1;
  /** @var Db */
  private $db;


  function __construct($content = null, $html = false, $char = null, $language = null, $db = null)
  {
    global $character;
    global $l;

    $char = (isset($char) && is_numeric($char)) ? $char : $character;
    $language = $language ? $language : $l;

    $this->character = $char;
    $this->language = $language;
    $this->content = $content;
    $this->html = $html;

    //replace tag should be last and double - because some other tags create replace tags ;)
    $this->basetags = array(new ObjNameTag(), new ReplaceTag(), new ReplaceTag());
    if ($db === null) {
      $db = Db::get();
    }
    $this->db = $db;
  }

  ////////////////////////////////////

  private function synhroniseBaseTags()
  {
    foreach ($this->basetags as $tagObj) {
      $tagObj->content = $this->content;
      $tagObj->html = $this->html;
      $tagObj->character = $this->character;
      $tagObj->language = $this->language;
    }
  }

  public function interpret()
  {
    $line = null;
    for ($i = 0; $i < $this->times; $i++) {
      if ($i > 0) {
        $this->content = $line;
      }
      $line = $this->interpretOnce();
    }
    return $line;
  }

  private function interpretOnce()
  {
    list ($usec, $sec) = explode(" ", microtime());
    $time = $usec + $sec % 10000000;

    $this->synhroniseBaseTags();
    foreach ($this->basetags as $tagObj)
      $this->content = $tagObj->interpret($this->content);

    $line = $this->content;

    $line = preg_replace_callback("/<CANTR (.*?)>/", array($this, 'tag_callback'), $line);

    if (strpos($line, "<CANTR ")) {
      foreach ($this->basetags as $tagObj)
        $line = $tagObj->interpret($line);
      $line = preg_replace_callback("/<CANTR (.*?)>/", array($this, 'tag_callback'), $line);
    }

    list ($usec, $sec) = explode(" ", microtime());
    $time = $usec + $sec % 10000000 - $time;
    $GLOBALS ['tagtime'] += $time;

    return "$line";
  }

  public function setTimes($times)
  {
    $this->times = $times;
  }

  private function generateFullCacheKey($keybase)
  {
    $html = $this->html ? '1' : '0';
    return $keybase . "{ html:$html }";
  }

  private function retrieveCache($key)
  {
    $cache_key = $this->generateFullCacheKey($key);

    return isset($this->cache_array[$cache_key]) ? $this->cache_array[$cache_key] : null;
  }

  private function storeCache($key, $value)
  {
    $cache_key = $this->generateFullCacheKey($key);
    return $this->cache_array[$cache_key] = $value;
  }

  function tag_callback($tag)
  {
    $cache = null;
    if (!defined("IGNORETAGCACHE")) {
      $cache = $this->retrieveCache($tag[0]);
    }
    if ($cache) {
      $line = $cache;
    } else {

      $command = explode(" ", $tag[1]);

      switch ($command[0]) {
        case "XCHARNAME":
        case "CHARNAME":
          $line = $this->interpretCharTag($command, $tag);
          break;
        case "LOCNAME":
          $line = $this->interpretLocNameTag($command, $tag);
          break;
        case "LOCDESC":
          $line = $this->interpretLocDescTag($command, $tag);
          break;
        case "LINK":
          //check function for comments
          $line = $this->interpretLinkTag($command, $tag);
          break;

        case "ANIMAL":
          $line = $this->interpretAnimalTag($command, $tag);
          break;

        default:
          return $tag[0];
      }

      if (!defined("IGNORETAGCACHE")) {
        $this->storeCache($tag[0], $line);
      }
    }

    return "$line";
  }

  private function interpretAnimalTag($command, $tag)
  {

    $name_info = explode("=", $command[1]);
    $animal_name = $name_info[1];

    $article = StringUtil::contains($tag[1], "WITHART");

    if (preg_match('/AMOUNT=(\d+)/', $tag[1], $amountinfo)) {  # how many animals (e. g. influences endings in Lithuanian)
      $amount = $amountinfo[1];
    } else {
      $amount = false;
    }

    if (preg_match('/GRAMMAR=(\w+)/', $tag[1], $grammarinfo)) {  # used for cases in German, Esperanto, Polish, ...
      $situation = $grammarinfo[1];
    } else {
      $situation = false;
    }

    $animal_is_valid = !empty($animal_name);
    if ($animal_is_valid) {
      $stm = $this->db->prepare("SELECT language, content, grammar FROM texts WHERE name = :name AND language IN (1, :language) ORDER BY language DESC LIMIT 2");
      $stm->bindStr("name", "animal_{$animal_name}_s");
      $stm->bindInt("language", $this->language);
      $stm->execute();
      if ($text_info = $stm->fetchObject()) {
        $animal = $text_info->content;
        $lang = $text_info->language;
        $grammar = $text_info->grammar;
        $line = generate_animal_desc($lang, $animal, $amount, $article, $grammar, $situation);
      } else {
        $line = "unknown animal: $animal_name";
      }
    } else {
      $line = "invalid animal name in $tag[1]";
    }

    return $line;
  }

  //this function change tags in format: "<CANTR LINK ARG1=value1 ARG2=value2 ...>" to text:  
  //index.php?character=$character&arg1=value1&arg2=value2&...
  //where $s is this user session, $character is actual active character
  private function interpretLinkTag($command, $tag)
  {

    $linkHref = "index.php?";
    for ($i = 1; $i < count($command); $i++) {
      $data = explode("=", $command[$i]);
      if (strtolower($data[0]) != 'hreftitle') {
        $linkHref .= strtolower($command[$i]) . '&';
      }
    }
    $linkHref = substr($linkHref, 0, strlen($linkHref) - 1);

    return $linkHref;
  }

  private function interpretCharTag($command, $tag)
  {

    $charinfo = explode("=", $command[1]);
    $charid = $charinfo[1];

    $grammar = "";
    if (preg_match('/GRAMMAR=(\w+)/', $tag[1], $grammarinfo)) {
      $grammar = $grammarinfo[1];
    }

    $charid_is_valid = !empty($charid) && is_numeric($charid);

    if (isset($this->charname_cache[$charid])) {
      $charname_line = $this->charname_cache[$charid][1];
      $foundchar = true;
    } else {
      $foundchar = false;
      if ($charid_is_valid) {

        $stm = $this->db->prepare("SELECT description, status, sex FROM chars WHERE id = :charId LIMIT 1");
        $stm->bindInt("charId", $charid);
        $stm->execute();
        $char_desc = $stm->fetchObject();

        if ($char_desc) {
          $foundchar = true;
          //* Translate description
          $chardescription = str_replace(" ", "_", $char_desc->description);
          $chardescription = "<CANTR REPLACE NAME=char_" . $chardescription . ">";
          if ($grammar) {
            $tag = new tag;
            $tag->language = $this->language;
            $tag->content = $chardescription;
            $chardescription = $tag->interpret();
            $chardescription = adjust_generic_charname($this->language, $chardescription, $grammar);
          }
        }
      }

      unset ($cached_name);

      if ((isset($this->character) && $charid != $this->character) || !$foundchar) {
        $stm = $this->db->prepare("SELECT name, description FROM charnaming
          WHERE observer = :observer AND observed = :observed AND type = :type LIMIT 1");
        $stm->bindInt("observer", $this->character);
        $stm->bindInt("observed", $charid);
        $stm->bindInt("type", NamingConstants::TYPE_CHAR);
        $stm->execute();
      } elseif ($charid == $this->character || $foundchar) {
        $stm = $this->db->prepare(" SELECT ch.name, ch.description FROM chars ch WHERE ch.id = :charId LIMIT 1");
        $stm->bindInt("charId", $charid);
        $stm->execute();
      }

      if ($charid_is_valid) {
        if (isset($cached_name)) {
          $charname_line = str_replace("<CANTR CHARDESC>", "$chardescription", $cached_name);
        } elseif (list ($fetched_name, $fetched_desc) = $stm->fetch(PDO::FETCH_NUM)) {
          $charname_line = str_replace("<CANTR CHARDESC>", "$chardescription", $fetched_name);
        } else {
          if ($foundchar) {
            $charname_line = $chardescription;
          } else {
            $charname_line = "an unknown person";
          }
        }
      } else {
        $charname_line = "an unknown person";
      }

      // Store the found description in the cache buffer
      $this->charname_cache[$charid][0] = $charid;
      $this->charname_cache[$charid][1] = $charname_line;
    }

    $line = "";
    if ($foundchar && $this->html) {
      $line .= "<a href=\"index.php?page=characterdescription&ocharid=$charid\" class=\"character char_$charid\">";
    }

    $line .= $charname_line;
    if ($foundchar && $this->html) {
      $line .= "</A>";
    }

    if ($this->admin) {
      $stm = $this->db->prepare("SELECT name,player FROM chars WHERE id = :charId LIMIT 1");
      $stm->bindInt("charId", $charid);
      $stm->execute();
      if ($charname_info = $stm->fetchObject()) {
        $line .= " ($charname_info->name of player $charname_info->player)";
      }
    }

    return $line;
  }

  private function interpretLocNameTag($command, $tag)
  {

    $locinfo = explode("=", $command[1]);
    $locid = $locinfo[1];

    $locid_is_valid = !empty($locid) && is_numeric($locid);

    if ($locid_is_valid) {
      $countStm = $this->db->prepare("SELECT COUNT(*) FROM charnaming WHERE observer = :observer AND observed = :observed AND type = :type LIMIT 1");
      $countStm->bindInt("observer", $this->character);
      $countStm->bindInt("observed", $locid);
      $countStm->bindInt("type", NamingConstants::TYPE_LOCATION);
      $count = $countStm->executeScalar();
      if ($count == 1) {
        $stm = $this->db->prepare("SELECT name FROM charnaming WHERE observer = :observer AND observed = :observed AND type = :type LIMIT 1");
        $stm->bindInt("observer", $this->character);
        $stm->bindInt("observed", $locid);
        $stm->bindInt("type", NamingConstants::TYPE_LOCATION);
        $stm->execute();
      } else {
        $stm = $this->db->prepare("SELECT name, area FROM locations WHERE id = :locationId LIMIT 1");
        $stm->bindInt("locationId", $locid);
        $stm->execute();
      }

      $locname_info = $stm->fetchObject();
      $locname_info->name = trim($locname_info->name);
      if ($locname_info->name == '') {
        $locname_info->name = "<CANTR REPLACE NAME=unnamed_location>";
      }

      if (isset($locname_info->area) && strstr($locname_info->name, "<CANTR REPLACE NAME=")) {
        $stm = $this->db->prepare("SELECT grammar FROM texts t INNER JOIN objecttypes ot ON ot.id = :objectTypeId
          WHERE t.name = CONCAT('item_', ot.unique_name, '_b') AND t.language = :language");
        $stm->bindInt("objectTypeId", $locname_info->area);
        $stm->bindInt("language", $this->language);
        $gender = $stm->executeScalar();
        $locname_info->name = str_replace("<CANTR REPLACE NAME=", "<CANTR REPLACE GENDER=$gender NAME=", $locname_info->name);
      }

      $line = "";
      if ($this->html) {
        $line .= "<A HREF=\"index.php?page=nameloc&id=$locid\" class=\"location loc_$locid\">";
      }

      $line .= $locname_info->name;
      if ($this->html) {
        $line .= "</A>";
      }
    }

    return $line;
  }

  private function interpretLocDescTag($command, $tag)
  {

    $locinfo = explode("=", $command[1]);
    $locid = $locinfo[1];

    $locid_is_valid = !empty($locid) && is_numeric($locid);

    if ($locid_is_valid) {
      $stm = $this->db->prepare("SELECT type, area FROM locations WHERE id = :locationId LIMIT 1");
      $stm->bindInt("locationId", $locid);
      $stm->execute();
      $loc_info = $stm->fetchObject();

      $stm = $this->db->prepare("SELECT unique_name, objectcategory FROM objecttypes WHERE id = :id LIMIT 1");
      $stm->bindInt("id", $loc_info->area);
      $stm->execute();
      $locdesc_info = $stm->fetchObject();

      if ($loc_info->type == LocationConstants::TYPE_VEHICLE && $locdesc_info->objectcategory == ObjectConstants::OBJCAT_DOMESTICATED_ANIMALS) {
        $stm = $this->db->prepare("SELECT grammar FROM texts WHERE name = :name AND language = :language");
        $stm->bindStr("name", "item_{$locdesc_info->unique_name}_b");
        $stm->bindInt("language", $this->language);
        $gender = $stm->executeScalar();

        $stm = $this->db->prepare("SELECT fullness FROM animal_domesticated WHERE from_location = :locationId");
        $stm->bindInt("locationId", $locid);
        $fullness = $stm->executeScalar();

        $line = Animal::getFedTagFromValue($fullness, $gender) . " <CANTR REPLACE NAME=item_" . $locdesc_info->unique_name . "_b>";
      } else {
        $line = "<CANTR REPLACE NAME=item_" . $locdesc_info->unique_name . "_b>";
      }
      if ($locdesc_info->unique_name == '') {
        $line = "<CANTR REPLACE NAME=unknown_location_type>";
      }
    }

    return $line;
  }
}
