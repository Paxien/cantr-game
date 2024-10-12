<?php


// To compile the templates for the first time move this file to www/ directory and enter the page directly

$rebuild = $_REQUEST["rebuild"];
$rebuildall = $_REQUEST["rebuildall"];

$TPLMAN_FILE = "index.php?page=tplman&";
if (!defined("_LIB_LOC")) {
  include_once "../lib/stddef.inc.php";
  include_once _LIB_LOC . "/header.functions.inc.php";
  $TPLMAN_FILE = "templatemgr.php?";
}

require_once(_LIB_LOC . "/func.templatemgr.inc.php");

set_time_limit(180);
?>

<h2 class='centered'>TEMPLATE MANAGER</h2>

<div style="margin:auto;width:700px;">
<pre>
  <a href="<?php echo $TPLMAN_FILE ?>page=tplman&rebuildall=1" style="font-size:12pt;font-weight:bold">Rebuild all</a>

  <?php

  define("_IN_COLUMN", 36);

  if (!function_exists("printDirectoryTplFiles")) {

    function printDirectoryTplFiles($dir = "", $ind = 0)
    {
      $globalId = 0;
      $d = dir("../tpl/" . $dir);

      while (false !== ($entry = $d->read())) {
        //ignore hidden files
        if ($entry[0] == ".") continue;
        if (is_dir("../tpl/$dir/$entry")) {
          $_dir = "$dir/$entry";
          $dirName = basename($_dir);
          for ($i = 0; $i < 4 * $ind; $i++) echo ' ';
          echo "<b>+ $dirName</b>\n";
          printDirectoryTplFiles($_dir, $ind + 1);
          $globalId++;
        } else {
          if (substr($entry, -4, 4) !== '.tpl') continue;
          $file = "$dir/$entry";
          $fileTo = urlencode($file);
          for ($i = 0; $i < 3 * $ind; $i++) echo ' ';
          echo "  <a href=\"" . $GLOBALS['TPLMAN_FILE'] . "rebuild=$fileTo\">Rebuild</a> " . basename($file) . "\n";
          $globalId++;
        }
        if ($globalId % _IN_COLUMN == 0) echo "</td>\n<td><br>";
      }

      $d->close();
    }

  }
  ?>

  <table>
    <tr style="vertical-align:top">
      <td>
        <?php printDirectoryTplFiles(); ?>
      </td>
    </tr>
  </table>

</pre>

  <?php

  if ($rebuild) {
    rebuild($rebuild);
  }

  if ($rebuildall) {

    function rebuildAllInDir($dir = "")
    {
      $d = dir("../tpl/" . $dir);

      while (false !== ($entry = $d->read())) {
        //ignore hidden files
        if ($entry[0] == ".") continue;
        if (is_dir("../tpl/$dir/$entry")) {
          $_dir = "$dir/$entry";
          rebuildAllInDir($_dir);
        } else {
          if (substr($entry, -4, 4) !== '.tpl') continue;
          $file = "$dir/$entry";
          rebuild($file);
        }
      }

      $d->close();
    }

    rebuildAllInDir();
  }

  ?>

  <div style="width: 100%; position: fixed; bottom: 10px; left: 10px;">
    <a href="index.php?page=player">
      <img src="<?php echo _IMAGES; ?>/button_back2.gif" title="Back"></a>
  </div>

</div>