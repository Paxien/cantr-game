<?php

function md5ForDependencies()
{
  $dirs = ["../www/js/", "../www/js/libs/", "../www/js/admin/", "../www/css/", "../www/css/skins/"];

  $hashes = [];
  foreach ($dirs as $dir) {
    $files = scandir($dir);
    foreach ($files as $file) { // for each file in directories
      if (in_array($file, [".", ".."])) {
        continue;
      }
      if (is_file($dir . $file)) {
        $hashes[] = md5_file($dir . $file); // get md5
      }
    }
  }

  $versionDir = "/ver/" . md5(implode($hashes));
  return $versionDir;
}

function rebuild($filename)
{
  $db = Db::get();
  $filename = urldecode($filename);
  if (strpos($filename, '..') !== false) {
    echo 'Give me a break..';
    exit;
  }

  if (file_exists(_ROOT_LOC . '/cache/i18n/pagetexts.ref.inc.php')) {
    include(_ROOT_LOC . '/cache/i18n/pagetexts.ref.inc.php');
  } else {
    $PageTexts = array();
  }

  unset ($PageTexts [$filename]);

  $langStm = $db->query("SELECT id, abbreviation FROM languages");
  $English = true;

  $filestring = "../tpl/" . $filename;
  $parts = pathinfo($filestring);
  if ($parts['extension'] != 'tpl') {
    die('File error.');
  }

  $md5Version = md5ForDependencies();
  while (list ($langID, $langAbbr) = $langStm->fetch(PDO::FETCH_NUM)) {

    $langs [] = $langAbbr;
    $Tags = [];

    $smarty = new CantrSmarty();
    $smarty->setLeftDelimiter("[");
    $smarty->setRightDelimiter("]");
    $smarty->assign("lang", $langAbbr);
    $smarty->assign("JS_VERSION", $md5Version);
    $smarty->setForceCompile(true);
    $smarty->setCaching(false);
    $query = [];

    $fileContents = file_get_contents($filestring);
    if ($fileContents === false) {
      die("can't open file " . $filestring);
    }

    preg_match_all("/\[[^\]]*\]/", $fileContents, $X);
    foreach ($X [0] as $tag) {
      if ($tag [1] == '$') {
        $striptag = substr($tag, 2, -1);
        if (!isset($Tags[$striptag])) {
          if ($striptag [0] != '_') {
            $query[] = $striptag;
          } else {
            $smarty->assign($striptag, constant($striptag));
          }
          $Tags [$striptag] = true;
        }

        if ($English) {
          $PageTexts [$filename][$striptag] = 1;
        }
      }
    }

    if ($query) {
      $stm = $db->prepareWithList("SELECT name, content FROM texts WHERE language = :language AND name IN (:names)", [
        "names" => $query,
      ]);
      $stm->bindInt("language", $langID);
      $stm->execute();

      // First set default English names
      if ($langID > 1) {
        if (count($DefText)) {
          foreach ($DefText as $name => $content)
            $smarty->assign($name, $content);
        }
      }

      // And then replace them with foreign language texts - if any
      while (list ($name, $content) = $stm->fetch(PDO::FETCH_NUM)) {
        $Info [$name][$langAbbr] = true;
        if ($content) {
          $smarty->assign($name, $content);
        } else {
          $smarty->assign($name, $DefText [$name]);
        }

        if ($langID == 1) {
          $DefText [$name] = $content;
        }
      }
    }

    $tempTplCode = file_get_contents(_LIB_LOC . "/../tpl" . $filename);
    $tempTplCode = preg_replace("/\[([^$])/", "&tempbrc;$1", $tempTplCode);
    // replace lonely "[" brackets, as they would be interpreted as smarty delim during the precompilation
    // during precompilation only [$TAGNAME] tags need to be interpreted to become static language-dependent texts

    // saving the temporary version with replaced brackets

    file_put_contents($smarty->getTemplateDir(0) . "temp_28173.tpl", $tempTplCode);


    ob_start();
    $smarty->display($smarty->getTemplateDir(0) . "temp_28173.tpl");
    $result = ob_get_contents();
    $result = preg_replace("/&tempbrc;/", "[", $result); // replace the placeholder for bracket with the original value
    ob_end_clean();
    unlink($smarty->getTemplateDir(0) . "temp_28173.tpl");

    $result = preg_replace("|#(\w+)#|", "{\$$1}", $result);

    $tPath = $smarty->getTemplateDir(0) . $filename;
    $tDir = dirname($tPath);
    if (!is_dir($tDir)) {
      mkdir($tDir);
    }

    file_put_contents($smarty->langTemplateName($tPath, $langAbbr), $result);

    $English = false;
  }

  $outfile = fopen(_ROOT_LOC . '/cache/i18n/pagetexts.ref.inc.php', "w");

  if (!$outfile) {
    die ("Could not open pagetexts.ref.inc.php for writing");
  }

  fwrite($outfile, "<?php\n\n// This is automatic generated script.\n// Do not alter it manually.\n\n\$PageTexts = array (\n");

  $count = 0;
  if ($n = count($PageTexts)) {
    foreach ($PageTexts as $page => $tags) {
      $count++;
      fwrite($outfile, "  \"$page\" => array (\n");
      $first = true;
      foreach ($tags as $tag => $one) {
        fwrite($outfile, ($first ? "" : ", \n") . "    \"$tag\" => 1");
        $first = false;
      }
      fwrite($outfile, ")" . ($count == $n ? "" : ",") . "\n");
    }
  }

  fwrite($outfile, ");\n\n?>");
  fclose($outfile);

  echo "<br><b><i><u>$filename </u></i></b>";
  echo "<pre><table><tr bgColor=\"blue\"><td><b>tag name</b></td>";
  foreach ($langs as $langStr)
    echo "<td><b>$langStr</b></td>";
  echo "</tr>";
  if (count($Info)) {
    foreach ($Info as $name => $langArr) {
      echo "<tr" . (++$count % 2 ? " bgColor=\"yellowgreen\"" : "") . "><td>$name</td>";
      foreach ($langs as $langStr)
        echo "<td>" . (isset($Info [$name][$langStr]) ? $langStr : "  ") . "</td>";
      echo "</tr>";
    }
  } else {
    echo "<tr><td colSpan=20 align=center><i>No tags used in this template file</i></td></tr>";
  }
  echo "</table></pre>";
}
