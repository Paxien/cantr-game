<?php

$smarty = new CantrSmarty;

// SANITIZE INPUT
$type = HTTPContext::getInteger('type');
$language = HTTPContext::getInteger('language');
$lang1 = HTTPContext::getInteger('lang1');
$lang2 = HTTPContext::getInteger('lang2');
$name = $_REQUEST['name'];
$content = $_REQUEST['content'];
$grammar = $_REQUEST['grammar'];
$download_option = $_REQUEST['downloadoption'];

if (!$lang1 || $lang1 == 0) {
  $lang1 = $l;
}
if (!$lang2 || $lang2 == 0) {
  $lang2 = 1;
}

$action = $_REQUEST['action'];
$previousaction = $_REQUEST['previousaction'];
$Rshowall = $_REQUEST['Rshowall'];
$tagsearch = $_REQUEST['tagsearch'];
$showall = $_REQUEST['showall'];
$searchfor = $_REQUEST['searchfor'];
$current_url = "index.php?page=managetranslations&action=$action&showall=$showall";
if ($tagsearch!=NULL) {
  $current_url .= "&tagsearch=$tagsearch";
}
if ($searchfor!=NULL) {
  $current_url .= "&searchfor=$searchfor";
}

function outputFile($texts,$filename,$isCSV) {
  $ext = pathinfo($filename)['extension'];
  $file_types = array( //Add more file types as needed.
    "csv" => "text/csv",
    "txt" => "text/plain",
    "tmx" => "application/xml");
  header("Content-Type: " . $file_types[$ext] . "; charset=utf-8");
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  header("Cache-Control: must-revalidate");
  header("Connection: close");
  flush();
  $file = fopen("php://output","w");
  if ($isCSV) {
    foreach ($texts as $text) {
      fputcsv($file, $text);
    }
  } else {
    foreach ($texts as $text) {
      fwrite($file, $text);
    }
  }
  fclose($file);
}

function sample_in_tag($tagname) {
  switch ($tagname[1]) {
  case "ACTION":
    $newcontent = "farm"; // or drop...
    break;
  case "ACTOR":
    $newcontent = 123456;
    break;
  case "AMOUNT":
    $newcontent = "300";
    break;
  case "ANIMAL":
    $newcontent = "bear";
    break;
  case "BUILDING":
    $newcontent = "Siom Medical Center";
    break;
  case "CHARACTER":
    $newcontent = "4";
    break;
  case "CURRSPEED":
    $newcontent = "100";
    break;
  case "DESC":
    $newcontent = "Pok Lighthouse";
    break;
  case "DEGREE":
    $newcontent = "120";
    break;
  case "DESTINATION":
    $newcontent = "Pok Harbour";
    break;
  case "DIRECTION":
    $newcontent = "180";
    break;
  case "END":
    $newcontent = "Pak";
    break;
  case "FOODTYPE":
    $newcontent = "potatoes";
    break;
  case "GRAMS":
    $newcontent = "50";
    break;
  case "ITEM":
    $newcontent = "crossbow";
    break;
  case "MATERIAL":
    $newcontent = "wood";
    break;
  case "MESSAGE":
    $newcontent = "Hello!";
    break;
  case "NAME":
    $newcontent = "Digging for potatoes";
    break;
  case "NUMBER":
    $newcontent = "436978";
    break;
  case "PAGE":
    $newcontent = "Newbie Guide";
    break;
  case "PERCENT":
    $newcontent = "31";
    break;
  case "PERCUP":
    $newcontent = "5";
    break;
  case "PERDAY":
    $newcontent = "90";
    break;
  case "PLACE":
    $newcontent = "Pok";
    break;
  case "ROAD":
    $newcontent = "1";
    break;
  case "ROADNAME":
    $newcontent = "Pok Beach Walk";
    break;
  case "SHIP":
    $newcontent = "Titanic";
    break;
  case "SHIPNAME":
    $newcontent = "Titanic";
    break;
  case "SPEED":
    $newcontent = "100";
    break;
  case "START":
    $newcontent = "Pok";
    break;
  case "TYPE":
    $newcontent = "(anything)";
    break;
  case "VEHICLE":
    $newcontent = "Pok Police Van";
    break;
  case "VICTIM":
    $newcontent = 234567;
    break;
  case "WATCHER":
    $newcontent = 345678;
    break;
  case "WEAPON":
    $newcontent = "axe";
    break;
  case "WEIGHT":
    $newcontent = "3015";
    break;
  default:
    $newcontent = $tagname[1];
  }
  return $newcontent;
}


function rebuildTipsFile(Db $db) {
  $stm = $db->query("SELECT id FROM languages ORDER BY id");
  $langs = $stm->fetchScalars();
  $allTranslations = [];

  $stm = $db->prepare("SELECT t_en.name, COALESCE(t_tr.content, t_en.content) AS text FROM texts t_en
        LEFT JOIN texts t_tr ON t_tr.name = t_en.name AND t_tr.language = :language
      WHERE t_en.language = 1 AND t_en.name LIKE 'tip\_%'");
  foreach ($langs as $lang) {
    $stm->bindInt("language", $lang);
    $stm->execute();
    $targetTranslation = [];
    foreach ($stm->fetchAll() as $transl) {
      $targetTranslation[$transl->name] = $transl->text;
    }
    $allTranslations[intval($lang)] = $targetTranslation;
  }

  $jsData = "var tipTexts = " . json_encode($allTranslations) . ";\n";
  file_put_contents(_LIB_LOC . "/../www/js/tooltip_texts.js", $jsData);
}

function getTexts($rawTexts, $lang1,$showall,$isMessage = false,$justContent = false) {
  if ($isMessage) {
    $name = 'id';
    $author = 'author';
    $updated = 'date';
  } else {
    $name = 'name';
    $author = 'translator';
    $updated = 'updated';
  }
  $translated = false;
  $texts = array();
  $previous = "";
  foreach ($rawTexts as $text) {
    $newText = array();
    if ($text->$name != $previous) {
      $translated = false;
      $previous = $text->$name;

      if (($text->language == $lang1) and ($showall == "no")) {
        $translated = true;
      } else {
        $newText['name'] = $text->$name;
        $translated = false;
      }

    } else {
        if (!$translated) {
          $newText['name'] = "";
        }
    }
    if (!$translated) {
      if ($justContent) {
        $newText['content'] = $text->content;
      } else {
        $newText['language'] = $text->language;
        $newText['updated'] = $text->$updated;
        $newText['translated'] = $translated;
        $newText['author'] = $text->$author;
        $newText['id'] = $text->$name;
      }
      array_push($texts,$newText);
    }
  }
  return $texts;
}

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_TRANSLATIONS)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

function fetchTextsFromDb($languages, Db $db)
{
  $stm = $db->prepareWithIntList("SELECT * FROM texts WHERE type=1 AND language IN (:languages) ORDER BY name,language DESC,updated DESC",
    ["languages" => $languages]);
  $stm->execute();
  return $stm->fetchAll();
}

function playerFullNameWithEmail(Player $playerInfo)
{
  return "{$playerInfo->getFullName()} <{$playerInfo->getEmail()}>";
}

  $db = Db::get();

  $smarty->assign("action",$action);
  $lang_url = "lang1=$lang1&lang2=$lang2";
  $innertemplate = $action;

  JsTranslations::getManager()->addTags(["js_confirm_language"]);
  switch ($action) {

  case "download" :
    break;
  case "downloadfile" :
    if ($download_option == "untranslated_tags") {
      $texts = getTexts(fetchTextsFromDb([$lang2, $lang1], $db), $lang1,"no",false,true);
      $filename = "Cantr_untranslated_tags_" . $langcode[$lang1] . ".csv";
      $content = array();
      foreach ($texts as $text) {
        array_push($content,array($text['name'],$text['content']));
      }
      outputFile($content,$filename,true);
    } else if ($download_option == "untranslated_content") {
      $texts = getTexts(fetchTextsFromDb([$lang2, $lang1], $db),$lang1,"no",false,true);
      $filename = "Cantr_untranslated_content_" . $langcode[$lang1] . ".txt";
      $content = array();
      foreach ($texts as $text) {
        array_push($content,$text['content'] . "\n");
      }
      outputFile($content,$filename,false);
    } else if ($download_option == "memory") {
      $previous = "";
      $first = null;
      $second = null;
      $filename = "Cantr_translation_memory_" . $langcode[$lang1] . "_" . $langcode[$lang2] . ".csv";
      $content = array();
      $texts = fetchTextsFromDb([$lang2, $lang1], $db);
      foreach($texts as $text) {
        if ($text->language == $lang2) {
          $first = $text;
        } else {
          $second = $text;
        }
        if ($first->name == $second->name) {
          array_push($content,array($first->content,$second->content));
        }
      }
      outputFile($content,$filename,true);
    } else if ($download_option == "memory_tags") {
      $texts = fetchTextsFromDb([$lang2, $lang1], $db);
      $previous = "";
      $first = null;
      $second = null;
      $filename = "Cantr_translation_memory_tags_" . $langcode[$lang1] . "_" . $langcode[$lang2] . ".csv";
      $content = array();
      foreach ($texts as $text) {
        if ($text->language == $lang2) {
          $first = $text;
        } else {
          $second = $text;
        }
        if ($first->name == $second->name) {
          array_push($content,array($first->name,$first->content,$second->content));
        }
      }
      outputFile($content,$filename,true);
    } else if ($download_option == "all_tags") {
      $previous = "";
      $first = null;
      $second = null;
      $filename = "Cantr_translation_all_tags_" . $langcode[$lang1] . "_" . $langcode[$lang2] . ".csv";
      $translated = array();
      $untranslated = array();
      $texts = fetchTextsFromDb([$lang2, $lang1], $db);
      foreach ($texts as $text) {
        if ($text->language == $lang2) {
          $first = $text;
        } else {
          $second = $text;
        }
        if ($first->name == $second->name) {
          array_push($translated,array($first->name,$first->content,$second->content));
          $first = null;
          $second = null;
        }
      }

      // Get untranslated
      $texts = getTexts(fetchTextsFromDb([$lang2, $lang1], $db),$lang1,"no",false,true);
      $content = array();
      foreach ($texts as $text) {
        array_push($untranslated,array($text['name'],$text['content']));
      }
      $all_tags = array_merge($translated,$untranslated);
      outputFile($all_tags,$filename,true);
    }
    exit;
    break;

  case "add1" :
    $innertemplate = "translate";
  case "translate" :
    $original_text = new stdClass();

    if ($action == "translate") {
      $stm = $db->prepare("SELECT * FROM texts WHERE name = :name AND language = :language AND type=1");
      $stm->bindStr("name", $name);
      $stm->bindInt("language", 1);
      $stm->execute();
      $original_text = $stm->fetchObject();
      $stm = $db->prepare("SELECT * FROM texts WHERE name = :name AND language = :language AND type=1");
      $stm->bindStr("name", $name);
      $stm->bindInt("language", $language);
      $stm->execute();
      $text = $stm->fetchObject();
      if (empty($text)) {
        $text = new StdClass();
        $text->language = $language;
        $text->grammar = "";
        $text->content = "";
        $text->name = $name;
      }
    }

    if ($action == "add1") {
      $text = new stdClass();
      $text->name = "";
      $text->language = 1;
      $text->content = "";
      $text->grammar = "";
      $type = 1;
    }

    $realcontent = $text->content;
    $text->content = htmlspecialchars($text->content);

    $original_text->content = htmlspecialchars($original_text->content);
    $isSample = false;
    if (strpos($realcontent, "#") > 0) {
      $isSample = true;
      $sample = preg_replace_callback ("/#(\w+)#/", "sample_in_tag", $realcontent);

      $tag = new tag;
      $tag->language = $text->language;
      $tag->character = -1; //propably not needed in this case, but the "tag" class does smth with it. Results in error when we don't set this variable 
      $tag->content = $sample;
      $sample = $tag->interpret();

      $smarty->assign("sample",$sample);
    }
    $smarty->assign("isSample",$isSample);
    $smarty->assign("text",$text);
    $smarty->assign("original_text",$original_text);
    if ($previousaction == "search") {
      $smarty->assign("previous_url","&action=search&searchfor=$searchfor");
    } else {
      $smarty->assign("previous_url","&showall=$showall");
    }
    $smarty->assign("nameType","name");
    break;

  case "translmess" :
    $innertemplate = "translate";
    $stm = $db->prepare("SELECT * FROM messages WHERE id = :id AND language = :language");
    $stm->bindInt("id", $id);
    $stm->bindInt("language", $language);
    $stm->execute();
    $text = $stm->fetchObject();

    $stm = $db->prepare("SELECT content FROM messages WHERE id = :id AND language = 1");
    $stm->bindInt("id", $id);
    $stm->execute();
    $original_text = $stm->fetchObject();
    $smarty->assign("text",$text);
    $smarty->assign("original_text",$original_text);
    if ($previousaction == "search") {
      $smarty->assign("previous_url","&action=search&searchfor=$searchfor");
    } else {
      $smarty->assign("previous_url","&showall=$showall");
    }
    $smarty->assign("nameType","id");
    break;

  case "store" :
    $stm = $db->prepare("SELECT COUNT(*) FROM texts WHERE name = :name AND type = 1 AND language = :language");
    $stm->bindStr("name", $name);
    $stm->bindInt("language", $language);
    $count = $stm->executeScalar();

    $date = date ( "Y-m-d" );

    $translator = playerFullNameWithEmail($playerInfo);
    if ($count) {
      $stm = $db->prepare("UPDATE texts SET content = :content, grammar = :grammar, updated = :updated,
                 translator = :translator WHERE name = :name AND type = 1 AND language = :language");
    } else {
      $stm = $db->prepare("INSERT INTO texts (name, type, language, content, grammar, updated, translator)
        VALUES (:name, 1, :language, :content, :grammar, :updated, :translator)");
    }
    $stm->bindStr("content", $content);
    $stm->bindStr("grammar", $grammar);
    $stm->bindStr("updated", $date);
    $stm->bindStr("translator", $translator);
    $stm->bindStr("name", $name);
    $stm->bindInt("language", $language);
    $stm->execute();


 		include _ROOT_LOC . '/cache/i18n/pagetexts.ref.inc.php';
		$text = $name;
		foreach ($PageTexts as $page => $texts) {
		  if ($PageTexts [$page][$name]) {
			  $rebuild = $page;
    		include 'templatemgr.php';
			}
    }

    if (strpos($name, "tip_") === 0) {
      rebuildTipsFile($db);
    }
    $new_url="";
    if ($previousaction == "search") {
      $new_url = "&action=$previousaction&searchfor=$searchfor";
    }
    cantr_redirect( "index.php?page=managetranslations$new_url&tagsearch=$tagsearch&showall=$showall&$lang_url" );
    return;
    break;

  case "storemess" :
    $stm = $db->prepare("SELECT COUNT(*) FROM messages WHERE id = :id AND language = :language");
    $stm->bindInt("id", $id);
    $stm->bindInt("language", $language);
    $count = $stm->executeScalar();
    $date = date ( "Y-m-d" );
    if ($count) {
      if ($language == 1) {
        $stm = $db->prepare("UPDATE messages SET content = :content, date = :date WHERE id = :id AND language = :language");
      } else {
        $stm = $db->prepare("UPDATE messages SET content = :content, date = :date, author = :author WHERE id = :id AND language = :language");
        $stm->bindStr("author", playerFullNameWithEmail($playerInfo));
      }
    } else {
      $stm = $db->prepare("INSERT INTO messages (id, language, content, date, author) VALUES (:id, :language, :content, :date, :author)");
      $stm->bindStr("author", playerFullNameWithEmail($playerInfo));
    }
    $stm->bindStr("content", $content);
    $stm->bindStr("date", $date);
    $stm->bindInt("id", $id);
    $stm->bindInt("language", $language);
    $stm->execute();

    $new_url = "";
    if ($previousaction == "search") {
      $new_url = "&action=$previousaction&searchfor=$searchfor";
    }
    cantr_redirect( "index.php?page=managetranslations$new_url&tagsearch=$tagsearch&showall=$showall&$lang_url" );
    return;
    break;

  case "search" :
    //* Actual search
    //If the tag name search was manually unchecked for a search, have it remain unchecked.
    if ($tagsearch == NULL) {
      $tagsearch = "no";
    }
    $previous = "";
    $output = "";
    $translated = false;
    $showall = "yes";

    if ($tagsearch == "yes") {
     $stm = $db->prepare("SELECT * FROM texts WHERE `name` LIKE :name AND type=1 ORDER BY name,language = :language DESC,language,updated DESC");
     $stm->bindStr("name", "%$searchfor%");
     $stm->bindInt("language", $lang1);
     $stm->execute();
     $rawTexts = $stm->fetchAll();
    } else {
     $stm = $db->prepare("SELECT * FROM texts WHERE `content` LIKE :content AND type=1 ORDER BY name,language DESC,updated DESC");
     $stm->bindStr("content", "%$searchfor%");
     $stm->execute();
     $rawTexts = $stm->fetchAll();
    }
    $texts = getTexts($rawTexts,$lang1,$showall);
    $smarty->assign("texts",$texts);
    break;

  case "select" :
     cantr_redirect( "index.php?page=managetranslations&tagsearch=yes&showall=$showall&lang1=$lang1&lang2=1" );
     return;

  default :
    $innertemplate = "default";

    if (empty($showall)) {
      $showall = "no";
    }

    switch ($Rshowall) {
    case "Yes":
     $showall = "yes";
     break;
    case "Updated":
     $showall = "updated";
     break;
    case "No":
     $showall = "no";
    }

    // Table with messages
    $stm = $db->prepareWithIntList("SELECT * FROM messages WHERE language IN (:languages) ORDER BY id,language DESC", [
      "languages" => [$lang2, $lang1],
    ]);
    $stm->execute();
    $messages = getTexts($stm->fetchAll(), $lang1,$showall,true);
    $smarty->assign("messages",$messages);

    //* Table with texts
    if (!isset($start)) { $start = 0; }
    if ($start < 0) { $start = 0; }

    if ($showall == "yes") {
      $stm = $db->prepareWithIntList("SELECT * FROM texts WHERE type = 1 AND language IN (:languages)
        ORDER BY name, language DESC, updated DESC LIMIT :limitStart, 150",
        ["languages" => [$lang2, $lang1]]);
      $stm->bindInt("limitStart", $start);
      $stm->execute();
      $rawTexts = $stm->fetchAll();
    } elseif ($showall == "updated") {
      $stm = $db->prepare("SELECT t1.* FROM texts t1
        LEFT JOIN texts t2 ON t2.name = t1.name AND t2.language = :lang2 AND t2.type = 1
        WHERE t1.type = 1 AND t1.language = :lang1
          AND t2.updated > t1.updated
        ORDER BY updated DESC, name
      ");
      $stm->bindInt("lang1", $lang1);
      $stm->bindInt("lang2", $lang2);
      $stm->execute();
      $rawTexts = $stm->fetchAll();
        // when translation in target language is older than the one in source language
    } else {
      $rawTexts = fetchTextsFromDb([$lang2, $lang1], $db);
    }

    $texts = getTexts($rawTexts, $lang1,$showall);
    $smarty->assign("texts",$texts);

    if ($showall == "yes") {
      $prev = $start - 148; $next = $start + 148;
      $smarty->assign("prev",$prev);
      $smarty->assign("next",$next);
    }
  }
  $smarty->assign("bracket1","[");
  $smarty->assign("bracket2","]");
  $smarty->assign("lang1",$lang1);
  $smarty->assign("lang2",$lang2);
  $smarty->assign("lang_url","lang1=$lang1&lang2=$lang2");
  $smarty->assign("type",$type);
  $smarty->assign("langcode",$langcode);
  $smarty->assign("showall",$showall);
  $smarty->assign("language",$language);
  $smarty->assign("searchfor",$searchfor);
  $smarty->assign("innertemplate",$innertemplate);
  $smarty->assign("action",$action);
  $smarty->assign("previousaction",$previousaction);
  $smarty->assign("tagsearch",$tagsearch);
  $smarty->assign("current_url",$current_url);

$smarty->displayLang ("translation/page.manage_translations.tpl", $lang_abr);