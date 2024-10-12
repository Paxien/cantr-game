<?php

chdir("../lib");

$effect = array();
function files($dir, &$effect) {
  $array = scandir($dir);
  foreach ($array as $file) {
    if (is_dir($dir ."/". $file)) {
      if (!in_array($file, array(".", "..", ".svn", "smarty", "3rdparty"))) {
        files($dir ."/". $file, $effect);
      }
    }
    else { // not dir
      $content = file_get_contents($dir ."/". $file);
      if (preg_match_all("/(class|interface) (.*?)( extends .*| implements .*)?(\s)?(\n)?(\s)?{/", $content, $out, PREG_SET_ORDER) ) {
        foreach ($out as $match) {
          if (strrchr($file, ".") == ".php") { // if it's code file
            $key = strtolower($match[2]); // get class/interface name
            if (isset($effect[$key])) { // disallow name conflicts - it would lead to non-deterministic result
              die("Name conflict for $key. Cannot continue");
            }
            $effect[$key] = substr($dir ."/". $file, 2);
          }
        }
      }
    }
  }
}

files(".", $effect);

$text = <<<'TEXT'
<?php
/**
 * This file is created automatically by script loader.php (probably located in storage/ directory)
 * Suggested way of running it: php loader.php
 * Don't move or edit this file
 */
class AutoLoader
{
  
  /**
    key - lowercase class name
    value - location relative to ./lib directory
  */
  
  private static $classes = array(

TEXT;
$buffer = "";
foreach ($effect as $name => $fileName) {
  $buffer .= "    \"$name\" => \"$fileName\",\n";
}

$text .= $buffer;

$text .= <<<'TEXT'
  );

  private function __construct () {}
  
  public static function getClassMap()
  {
    return self::$classes;
  }
}

TEXT;

echo $text;

file_put_contents("classes/AutoLoader.php", $text);

