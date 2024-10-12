<?php

require_once('../lib/stddef.inc.php');
require_once('../lib/header.functions.inc.php');
// Prevent caching
header("Expires: Mon, 20 Dec 1998 01:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

define("acNode", 0);
define("acURL", 1);
define("acAjax", 2);


// Action class - defines node click action
class Action
{
  var
    $Type,
    $ParamStr;

  function Action($Type = 0, $Data = "", $Dest = "")
  {
    $this->Type = $Type;
  }

  function AddParams($Params)
  {
    if (!$Params) {
      return;
    }
    foreach ($Params as $Name => $Val)
      $this->ParamStr .= ($this->ParamStr ? "&" : "") . urlencode($Name) . '=' . urlencode($Val);
  }
}

// TreeNode class - contains node data
class TreeNode
{
  var
    $Type,
    $Class,
    $IconOpens,
    $Text,
    $Action;

  function TreeNode($Class = "bmfold", $Text = "Unnamed", $Action = "", $ActionType = 0)
  {
    $this->Type = "TreeNode";
    $this->Class = $Class;
    $this->IconOpens = true;
    $this->Text = $Text;
  }

  function SetActionRef($Action)
  {
    $this->Action = $Action;
  }

  function SetAction($Type = 0, $Data = "", $Dest = "")
  {
    $this->Action = new Action ($Type, $Data, $Dest);
  }

  function SetNodeAction($Request, $Params = null)
  {
    $this->Action = new Action (acNode);
    $this->Action->AddParams(array("request" => $Request));
    $this->Action->AddParams($Params);
  }

  function SetURLAction($URL, $Params = null)
  {
    $this->Action = new Action (acURL);
    $this->Action->URL = $URL;
    $this->Action->AddParams($Params);
  }
}

class AjaxMessage
{
  var
    $Type,
    $Class,
    $Text;

  function AjaxMessage($Text, $Class = "ajaxmsg")
  {
    $this->Type = "Message";
    $this->Class = $Class;
    $this->Text = $Text;
  }
}

// TreeNodes class - container for TreeNode objects
class TreeNodes
{
  var
    $Type,
    $Nodes;

  function TreeNodes($Nodes)
  {
    $this->Type = "TreeNodes";
    $this->Nodes = $Nodes;
  }
}

// Just in case, set to english if no lang available
if (!$l) {
  $l = 1;
}

// Translates $name tag
function GetLangText($name)
{
  static $tag;
  $tag = new tag;
  $tag->language = $GLOBALS ['l'];
  $tag->content = "<CANTR REPLACE NAME=" . str_replace(" ", "_", $name) . ">";
  return $tag->interpret();
}

// $Children - subcategories defined as an array (parent_id => child_id)
function GetChildrenCount($id, &$Categories, &$Children)
{
  $Count = $Categories [$id]->items;
  foreach ((array) $Children [$id] as $Child) {
    $Count += GetChildrenCount($Child, $Categories, $Children);
  }
  return $Count;
}

$stxt = urldecode($_REQUEST['stxt']);
$request = $_REQUEST['request'];
$catid = $_REQUEST['catid'];
$l = HTTPContext::getInteger("l");
$ch = $_REQUEST['ch'];
$objid = $_REQUEST['objid'];

$db = Db::get();

switch ($request) {

  // Category contents
  case "cat":
    $escSearchText = $db->quote("%$stxt%");
    // Language other than english, so search in booth - English and native languages
    // some objects has their names as item_unique_name_o, other as item_unique_name_b
    if ($l > 1) {
      $selectLang = ", t1.content AS lbuildname, t2.content AS lname, t3.content AS buildname, t4.content AS gname ";
      $fromLang =
        "LEFT JOIN texts t1 ON t1.language = $l AND t1.name = CONCAT('item_', o.unique_name, '_b')\n" .
        "LEFT JOIN texts t2 ON t2.language = $l AND t2.name = CONCAT('item_', o.unique_name, '_o')\n" .
        "LEFT JOIN texts t3 ON t3.language = 1 AND t3.name = CONCAT('item_', o.unique_name, '_b')\n" .
        "LEFT JOIN texts t4 ON t4.language = 1 AND t4.name = CONCAT('item_', o.unique_name, '_o')\n";
      if ($stxt) {
        // Search text defined
        $AddSelect = ", SUM(
              lower(t1.content) LIKE lower($escSearchText)
              OR lower(t2.content) LIKE lower($escSearchText)
              OR lower(t3.content) LIKE lower($escSearchText)
              OR lower(t4.content) LIKE lower($escSearchText)
            ) AS items";
      } else { // No searching
        $AddSelect = ", COUNT(o.id) AS items";
      }
    } else {
      // English language, so searching is simplier
      $selectLang = ", t1.content AS lbuildname, t2.content AS lname";
      $fromLang =
        "LEFT JOIN texts t1 ON t1.language = $l AND t1.name = CONCAT('item_', o.unique_name, '_b')\n" .
        "LEFT JOIN texts t2 ON t2.language = $l AND t2.name = CONCAT('item_', o.unique_name, '_o')\n";
      $catSelectLang = ", SUM(t1.name IS NOT NULL OR t2.name IS NOT NULL) AS items";
      if ($stxt) {
        $AddSelect = ", SUM(
            lower(t1.content) LIKE lower($escSearchText)
            OR lower(t2.content) LIKE lower($escSearchText)
          ) AS items";
      } else {
        $AddSelect = ", COUNT(o.id) AS items";
      }
    }

    $condition = $catid ? '= ' . $catid : 'IS NULL';
    $stm = $db->query("
        SELECT oc.id, oc.name, oc.parent $AddSelect 
        FROM objectcategories oc 
        LEFT JOIN objecttypes o ON (o.objectcategory = oc.id)
        $fromLang
        WHERE status = 0
        GROUP BY oc.id
        ORDER BY name
      ");

    foreach ($stm->fetchAll() as $X) {
      $Categories [$X->id] = $X;
    }

    // Build category tree
    foreach ($Categories as $X) {
      if ($X->parent) {
        $Children [$X->parent][] = $X->id;
      }
    }

    // Dump categories
    foreach ($Categories as $X) {
      $items = GetChildrenCount($X->id, $Categories, $Children);
      if ($items && $X->parent == $catid) {
        $Name = GetLangText("category_$X->name");
        $node = new TreeNode ("bmfold", $Name . " <span class=\"linkannt\">($items)</span>");
        $node->SetNodeAction("cat", array("catid" => $X->id, "stxt" => $stxt));
        $AJAXOut [] = clone $node;

        $C++;
      }
    }

    // Objects selection
    $stm = $db->query("
        SELECT o.id $selectLang $AddSelect 
        FROM objecttypes o
        $fromLang
        WHERE objectcategory $condition
        GROUP BY o.id 
        ORDER BY lname
      ");

    foreach ($stm->fetchAll() as $X) {
      if ($X->items > 0) {
        $Name = $X->buildname != "" ? $X->buildname : $X->gname;
        if ($X->lbuildname != "" || $X->lname != "") {
          $Name = ($X->lbuildname != "") ? $X->lbuildname : ($X->lname != null ? $X->lname : "") . ($Name ? " <span class=\"linkeng\">[$Name]</span>" : "");
        }
        $node = new TreeNode ("bmitem", $Name);
        $node->SetNodeAction("obj", array("objid" => $X->id));
        $AJAXOut [] = $node;
        $C++;
      }
    }

    if (!$C) {
      $AJAXOut = new AjaxMessage (GetLangText("search_not_found"));
    }

    break;

  case "obj":
    $node = new TreeNode ("bmproceed", "<span style=\"color: #faa\">" . GetLangText("build_proceed") . "</span>");
    $node->SetURLAction("index.php", array("page" => "build", "character" => $ch, "objecttype" => $objid));
    $AJAXOut [] = $node;

    $stm = $db->prepare("SELECT ot.*, oc.parent parentcategory FROM objecttypes ot
      LEFT JOIN objectcategories oc ON ot.objectcategory = oc.id WHERE ot.id = :objectTypeId");
    $stm->bindInt("objectTypeId", $objid);
    $stm->execute();
    $X = $stm->fetchObject();
    include _LIB_LOC . '/func.rules.inc.php';

    $R = explodeBuildReq($X->build_requirements);
    $C = explodeBuildReq($X->build_conditions);

    $or = GetLangText("or");

    if ($X->parentcategory == _OBJ_CATEGORY_CLOTHES) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("cloth_desc_" . $X->unique_name));
    }


    if ($R->days) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("days_needed") . " " . str_replace(".", ".<small>", $R->days));
    }
    if ($R->tools) {
      foreach ($R->tools as $tool => $q) {
        $WasTool = false;
        $stm = $db->prepare("SELECT id, unique_name FROM objecttypes WHERE name = :name AND objectcategory NOT IN (1, 5, 12, 16, 17, 18)");
        $stm->bindStr("name", $tool);
        $stm->execute();
        while (list ($id, $UniqueName) = $stm->fetch(PDO::FETCH_NUM)) {
          $node = new TreeNode ($WasTool ? "bmepty" : "bmtool", ($WasTool ? "<i>$or</i> " : "") . GetLangText("item_" . $UniqueName . "_o"));
          $node->SetNodeAction("obj", array("objid" => $id));
          $AJAXOut [] = $node;
          $WasTool = true;
        }
      }
    }

    if ($R->raws) {
      foreach ($R->raws as $raw => $weight) {
        $lraw = str_replace("]", "]</span>", str_replace("[", " <span class=\"linkeng\">[", GetLangText("raw_$raw")));
        $AJAXOut [] = new TreeNode ("bmraw", "$weight " . GetLangText("grams_of") . " " . $lraw);
      }
    }


    if ($R->objects) {
      foreach ($R->objects as $object => $count) {
        $WasTool = false;
        $stm = $db->prepare("SELECT id, unique_name FROM objecttypes WHERE name = :name  AND objectcategory NOT IN (1, 5, 12, 16, 17, 18)");
        $stm->bindStr("name", $object);
        $stm->execute();
        while (list ($id, $UniqueName) = $stm->fetch(PDO::FETCH_NUM)) {

          $Text = ($WasTool ? "<i>$or</i> " : "") . GetLangText("item_" . str_replace(" ", "_", $UniqueName) . "_o");
          if ($count > 1) {
            $Text .= " <span class=\"linkannt\">($count items)</span>";
          }
          $node = new TreeNode ($WasTool ? "bmepty" : "bmpart", $Text);
          $node->SetNodeAction("obj", array("objid" => $id));
          $AJAXOut [] = $node;
          $WasTool = true;
        }
      }
    }

    if ($C->hasobject) {
      foreach ($C->hasobject as $object => $q) {
        $WasTool = false;
        $stm = $db->prepare("SELECT id, unique_name FROM objecttypes WHERE name = :name AND objectcategory NOT IN (1, 5, 12, 16, 17, 18)");
        $stm->bindStr("name", $object);
        $stm->execute();
        while (list ($id, $UniqueName) = $stm->fetch(PDO::FETCH_NUM)) {
          $node = new TreeNode ($WasTool ? "bmepty" : "bmmachine", ($WasTool ? "<i>$or</i> " : "") . GetLangText("item_" . $UniqueName . "_o"));
          $node->SetNodeAction("obj", array("objid" => $id));
          $AJAXOut [] = $node;
          $WasTool = true;
        }
      }
    }

    if ($C->isbuilding && !$C->isnotvehicle) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("build_in_buildings"));
    }
    if ($C->isbuilding && $C->isnotvehicle) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("build_in_buildings_on_land"));
    }
    if ($C->isnoncentertype) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("build_in_buildings_vehicles"));
    }
    if ($C->islocation) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("build_outside"));
    }
    if ($C->isvehicle) {
      $AJAXOut [] = new TreeNode ("bmdot", GetLangText("build_in_vehicles"));
    }
    break;

  default:
    $AJAXOut = new AjaxMessage ("Invalid operation.");

}

if (gettype($AJAXOut) == 'array') {
  $AJAXOut = new TreeNodes ($AJAXOut);
}

$JSONOut = json_encode($AJAXOut);

echo $JSONOut;
