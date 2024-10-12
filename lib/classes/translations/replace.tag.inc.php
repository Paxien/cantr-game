<?php

require_once "basetag.inc.php";

class ReplaceTag extends BaseTag
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
    if ($content) {
      $this->content = $content;
    }

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
    if (!$content) {
      $content = $this->content;
    }

    preg_match_all("/<CANTR\s+REPLACE(\s+(.*?(NAME=[^\s>]*).*?))?\>/", $content, $matches, PREG_SET_ORDER);

    foreach ($matches as $rTag) {
      $text = $rTag[0];
      $nameArg = $rTag[3];

      $regNameArg = str_replace('/', '\/', $nameArg);
      $otherArg = preg_replace("/\s*$regNameArg\s*/", ' ', $rTag[2]);
      $otherArg = trim($otherArg);

      $this->interpretReplaceTag($text, $nameArg, $otherArg);
    }
  }

  private function interpretReplaceTag($orginal, $nameArg, $args)
  {

    if (isset($this->buffer[$orginal])) {
      return;
    }

    $textnameA = explode("=", $nameArg);
    $textname = strtolower($textnameA[1]);

    $replaceObject = new StdClass();
    $replaceObject->name = $textname;
    $replaceObject->args = array();

    //args (removed double and more whitespaces)
    $args = preg_replace('/\s+/', ' ', $args);
    $args = explode(' ', $args);

    if (count($args) > 0) {
      $actors = "";
      foreach ($args as $arg) {

        $argInfo = explode("=", $arg);
        if ($argInfo[0] == "ACTORS") {
          //actors arg is changed by default
          $actors .= (empty($actors) ? "" : ", ") . "<CANTR CHARNAME ID=" . $argInfo[1] . ">";
          $argInfo[1] = $actors;
        }
        if (!isset($argInfo[1])) {
          $argInfo[1] = "";
        }
        $replaceObject->args["#$argInfo[0]#"] = urldecode($argInfo[1]);
      }
    }

    $this->buffer[$orginal] = $replaceObject;
  }

  private function apply_buffered($content = null)
  {
    if (!$content) {
      $content = $this->content;
    }

    $tagsQueue = $this->apply_bufferedQueue();
    foreach ($tagsQueue as $tag => $tagValue) {
      $content = str_replace($tag, $tagValue, $content);
    }

    return $content;
  }

  //returning queue with <CANTR REPLACE..> and value to replacing 
  private function apply_bufferedQueue()
  {

    if (count($this->buffer) == 0) {
      return array();
    }

    $replaceQuery = "SELECT lower( name ) AS name, language, content FROM texts WHERE ";
    $replaceQuery .= ((isset($this->language)) ? "language IN (1, $this->language)" : "language = 1") . " AND name IN (";

    $escapedNames = $this->getEscapedTagNames();
    $replaceQuery .= implode(", ", $escapedNames);
    $replaceQuery .= ") ORDER BY name";

    $stm = $this->db->query($replaceQuery);

    $data = array();
    while ($obj = $stm->fetchObject()) {
      //prefer user language over english
      if (isset($data[$obj->name]) && $data[$obj->name]->language == $this->language) {
        continue;
      }
      $data[$obj->name] = $obj;
    }


    $queue = array();
    foreach ($this->buffer as $tagname => $replaceObj) {
      //check that tag has downloaded data 

      $toReplace = isset($data[$replaceObj->name]) ? $data[$replaceObj->name]->content : '';
      //if yes - append all his args
      foreach ($replaceObj->args as $argName => $argValue) {
        $toReplace = str_replace($argName, $argValue, $toReplace);
      }
      //replace <CANTR REPLACE..> to real data

      $queue[$tagname] = $toReplace;
    }
    return $queue;
  }

  /**
   * @return array of escaped tag names
   */
  private function getEscapedTagNames()
  {
    $names = [];
    foreach ($this->buffer as $replaceObj) {
      $names[$replaceObj->name] = true;
    }
    $escapedNames = [];
    foreach (array_keys($names) as $name) {
      $escapedNames[] = $this->db->quote($name);
    }
    return $escapedNames;
  }
}
