<?php

require_once "basetag.inc.php";

class ObjNameTag extends BaseTag
{

  //associated array - orginal <CANTR REPLACE> tag and object with replace effect.
  var $buffer;

  //replace tag can be use with array of tags - this ofcourse will generate one query.
  public function interpretQueue($tagsArray)
  {
    $this->begin_buffering();
    foreach ($tagsArray as $tag) {
      $this->buffer_all($tag);
    }
    $resolvedQueue = $this->apply_bufferedQueue();

    return $resolvedQueue;
  }

  public function interpret($content = null)
  {
    if ($content) $this->content = $content;

    $this->begin_buffering();
    $this->buffer_all();

    return $this->apply_buffered();
  }

  private function begin_buffering()
  {
    $this->buffer = array();
  }

  private function buffer_all($content = null)
  {
    if (!$content) $content = $this->content;

    preg_match_all("/<CANTR\s+OBJNAME(\s+(.*?(ID=[^\s>]*).*?))?>/", $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $rTag) {
      $text = $rTag[0];
      $idArg = $rTag[3];
      $otherArg = preg_replace("/\s*$rTag[3]\s*/", ' ', $rTag[2]);
      $otherArg = trim($otherArg);

      $this->interpretObjNameTag($text, $idArg, $otherArg);
    }
  }

  private function interpretObjNameTag($orginal, $idArg, $args)
  {

    if (isset($this->buffer[$orginal])) return;

    $idinfoA = explode("=", $idArg);
    $objid = $idinfoA[1];
    if (empty($objid) || !is_numeric($objid)) return;

    $type = 0;
    if (preg_match('/TYPE=(\d+)/', $args, $typeinfo)) {
      $type = intval($typeinfo[1]);
    }

    if (preg_match('/GRAMMAR=(\w+)/', $args, $grammarinfo)) {
      $grammar = $grammarinfo[1];
    }

    $replObj = new StdClass();
    $replObj->tagsubtype = $type;
    $replObj->grammar = isset($grammar) ? $grammar : "Nom";
    $replObj->objid = intval($objid);
    $replObj->orginal = $orginal;

    $this->buffer[$objid] = $replObj;
  }

  private function apply_buffered($content = null)
  {
    if (!$content) $content = $this->content;

    $tagsQueue = $this->apply_bufferedQueue();
    //echo count($tagsQueue);
    foreach ($tagsQueue as $tag => $tagValue) {
      $content = str_replace($tag, $tagValue, $content);
    }

    return $content;
  }

  //returning queue with <CANTR OBJNAME..> and value to replacing 
  private function apply_bufferedQueue()
  {

    if (count($this->buffer) == 0) return array();

    $objQuery = "SELECT o.*, ot.unique_name, ot.subtable,
       IF(ot.deter_rate_turn > 0 && ot.deter_visible > 0, 1, 0) as showdeterioration
       FROM objects o LEFT JOIN objecttypes ot ON ot.id = o.type WHERE ";

    $counter = 0;
    foreach ($this->buffer as $id => $rObj) {
      if ($id == 15) continue;
      $counter++;
      $objQuery .= "o.id='$id' OR ";
    }
    //cut last "OR "
    if ($counter > 0) $objQuery = substr($objQuery, 0, -3);
    else
      //we dont want any data in this case.
      $objQuery .= '1 = 0';
    $hObjInfo = $this->db->query($objQuery);

    $data = array();
    $rawData = array();
    foreach ($hObjInfo->fetchAll() as $obj) {
      $obj->tagsubtype = $this->buffer[$obj->id]->tagsubtype;
      $obj->grammar = $this->buffer[$obj->id]->grammar;

      if ($obj->type == 2) {
        $rawData [] = $obj;
      } else {
        $data[$obj->id] = $obj;
      }
    }
    if (count($data) > 0) $this->prepareObjectData($data);
    if (count($rawData) > 0) $this->prepareRawSpecialData($rawData, $data);
    $this->translateInnerTags($data);

    $queue = array();
    foreach ($this->buffer as $id => $rObj) {
      //check that tag has downloaded data 
      $tagData = $data[$id];

      $toreplace = isset($tagData) ? $tagData->name : '<i>(Item is missing)</i>';
      if ($id == 15) $toreplace = $this->getTranslatedFists();

      //replace <CANTR REPLACE..> to real data      
      $queue[$rObj->orginal] = $toreplace;
    }
    return $queue;
  }

  var $translatedFists;

  private function getTranslatedFists()
  {
    if (!$this->translatedFists) {
      $stm = $this->db->prepare("SELECT content FROM texts WHERE language IN (1, :language)
        AND name LIKE 'weapon_bare_fist_in_event' ORDER BY language DESC LIMIT 1");
      $stm->bindInt("language", $this->language);
      $this->translatedFists = $stm->executeScalar();
    }
    return $this->translatedFists;
  }

  //this tag should be used to translate normal objects I think, but this code
  //can help with some types of raw objects.  
  private function prepareRawSpecialData($rawData, &$data)
  {
    $rawQuery = "SELECT id, t.content as name
      FROM rawtypes rt LEFT JOIN texts t ON t.name = CONCAT( 'raw_', REPLACE(rt.name, ' ', '_') )
      WHERE language IN (1, :language) AND ( ";
    foreach ($rawData as $raw) {
      $rawQuery .= "rt.id = $raw->typeid OR ";
    }
    //cut last "OR "
    $rawQuery = substr($rawQuery, 0, -3) . ")";
    $stm = $this->db->prepare($rawQuery);
    $stm->bindInt("language", $this->language);
    $stm->execute();
    $typeNames = array();
    while (list($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
      $typeNames[$id] = $name;
    }

    foreach ($rawData as $raw) {
      $raw->name = $typeNames[$raw->typeid];
      switch ($raw->tagsubtype) {
        case 1:
          $raw->name = "<CANTR REPLACE NAME=some> $raw->name";
          break;
        default:
          $raw->name = "$raw->weight <CANTR REPLACE NAME=grams_of> $raw->name";
      }

      $data[$raw->id] = $raw;
    }
  }

  private function prepareObjectData(&$data)
  {

    $oNamingQuery = "SELECT SUBSTRING(name, 6 ) as name, content, grammar as gender FROM texts
      WHERE language IN (1, :language) AND ( ";

    foreach ($data as $obj) {
      $oNamingQuery .= "name LIKE 'item\_" . $obj->unique_name . "\__' OR ";
    }
    //cut last "OR "
    $oNamingQuery = substr($oNamingQuery, 0, -3) . ")";
    $stm = $this->db->prepare($oNamingQuery);
    $stm->bindInt("language", $this->language);
    $stm->execute();
    $typeNames = array();
    while (list($name, $translName, $gender) = $stm->fetch(PDO::FETCH_NUM)) {
      $typeNames[$name] = new StdClass();
      $typeNames[$name]->name = $translName;
      $typeNames[$name]->gender = $gender;
    }

    foreach ($data as $obj) {
      $selType = null;
      switch ($obj->tagsubtype) {
        case 1:
          $tName = $obj->unique_name . "_t";
          $selType = isset($typeNames[$tName]) ? $typeNames[$tName] : $typeNames[$obj->unique_name . "_o"];
          break;
        default:
          $selType = $typeNames[$obj->unique_name . "_o"];
      }
      $obj->name = $selType->name;
      if ($obj->showdeterioration) {
        $obj->name = $this->get_deter_descr($obj->name, $obj->deterioration, $obj->grammar, $selType->gender);
      }
    }
  }

  var $cachedDeterDesc = null;

  private function get_deter_descr($itemName, $deterioration, $grammar, $gender)
  {

    if ($deterioration < 2500)
      $det = "det_brandnew";
    elseif ($deterioration < 5000)
      $det = "det_new";
    elseif ($deterioration < 6250)
      $det = "det_used";
    elseif ($deterioration < 7500)
      $det = "det_often-used";
    elseif ($deterioration < 8750)
      $det = "det_old";
    else
      $det = "det_crumbling";

    if (!isset($this->cachedDeterDesc)) {
      $stm = $this->db->prepare("SELECT name, content FROM texts WHERE name in ('det_brandnew', 'det_new', 'det_used', 'det_often-used', 'det_old', 'det_crumbling') AND
        language IN (1, :language) ORDER BY language DESC LIMIT 6");
      $stm->bindInt("language", $this->language);
      $stm->execute();
      while (list($name, $tr) = $stm->fetch(PDO::FETCH_NUM)) {
        $this->cachedDeterDesc[$name] = $tr;
      }
    }

    return adjust_nounphrase($this->language, "", $this->cachedDeterDesc[$det], $itemName, $grammar, $gender);
  }

  //translate special text that object name can have: #columnname#, #subtable.columnname#
  private function translateInnerTags(&$data)
  {
    $toProcess = array();
    foreach ($data as $id => $obj) {
      //store data from to download by subtable type
      if (preg_match_all("/#subtable\.(.*?)#/", $obj->name, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
          $info = new StdClass();
          //we always use "typeid" as id for subtables
          $info->id = $id;
          $info->subtableid = $obj->typeid;
          $info->column = $match[1];
          $info->orginal = $match[0];

          $toProcess[$obj->subtable] [] = $info;
        }
      } elseif (preg_match_all("/#(.*?)#/", $obj->name, $matches, PREG_SET_ORDER)) {
        //replace that from objects table
        foreach ($matches as $match) {
          //get value from our before getted data (object table)
          $column = $match[1];
          $value = property_exists($obj, $column) ? $obj->$column : '';
          $obj->name = str_replace($match[0], TextFormat::getDistinctHtmlText($value), $obj->name);
        }
      }
    }

    foreach ($toProcess as $subtable => $infoArray) {
      $selString = 'id, ';
      foreach ($infoArray as $info) {
        $selString .= "$info->column, ";
      }
      $selString = substr($selString, 0, -2);

      $subtableQuery = "SELECT $selString FROM $subtable WHERE ";
      foreach ($infoArray as $info) {
        $subtableQuery .= "id = $info->subtableid OR ";
      }
      $subtableQuery = substr($subtableQuery, 0, -3);

      $stm = $this->db->query($subtableQuery);

      $subtableData = array();
      while ($o = $stm->fetch(PDO::FETCH_ASSOC)) {
        $id = $o['id'];
        $subtableData[$id] = $o;
      }

      foreach ($infoArray as $info) {
        $value = "";
        if (isset($subtableData[$info->subtableid])) {
          $value = $subtableData[$info->subtableid][$info->column];
        }
        $data[$info->id]->name = str_replace($info->orginal, $value, $data[$info->id]->name);
      }
    }
  }
}
