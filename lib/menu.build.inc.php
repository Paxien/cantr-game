<?php

$parent = HTTPContext::getInteger('parent', null);

show_title("<CANTR REPLACE NAME=title_build_menu>");

echo "<div class=page>";
echo "<TABLE>";

echo "<TR><TD COLSPAN=2><CANTR REPLACE NAME=page_buildmenu_select><BR><BR>";
$parentCondition = $parent ? '= '.$parent : "IS NULL";

$db = Db::get();
$stm = $db->prepare("SELECT id, name FROM objectcategories WHERE parent $parentCondition AND status = 0 ORDER BY name");
$stm->bindInt("parent", $parent, true);
$stm->execute();
while (list ($id, $name) = $stm->fetch(PDO::FETCH_NUM)) {
  $name = str_replace (" ", "_", $name);
  echo "<TR><TD WIDTH=30 vAlign=top><FORM METHOD=POST ACTION=\"index.php?page=build&noJavaScript=1&parent=$id\">";
  echo "<INPUT TYPE=hidden NAME=character VALUE=$character>";
  echo "<INPUT TYPE=image SRC=\"" . _IMAGES . "/button_small_menu.gif\" title=\"<CANTR REPLACE NAME=page_buildmenu_2>\">";
  echo "</FORM></TD><TD><b><CANTR REPLACE NAME=category_$name></b></TD></TR>";
}

$stm = $db->prepare("SELECT * FROM objecttypes WHERE objectcategory $parentCondition ORDER BY name");
$stm->execute();
foreach ($stm->fetchAll() as $objecttype_info) {

  echo "<TR><TD WIDTH=30><FORM METHOD=POST ACTION=\"index.php?page=build\">";
  echo "<INPUT TYPE=hidden NAME=character VALUE=$character>";
  echo "<INPUT TYPE=hidden NAME=objecttype VALUE=$objecttype_info->id>";
  echo "<INPUT TYPE=image SRC=\"" . _IMAGES . "/button_small_menu.gif\" title=\"<CANTR REPLACE NAME=page_buildmenu_4>\">";
  $objectname = TagUtil::getGenericTagForObjectName($objecttype_info->unique_name);
  $objectname = "<CANTR REPLACE NAME=$objectname>";
  echo "</FORM></TD><TD>$objectname</TD></TR>";
}

if ($parent) {
  // get its parent
  $stm = $db->prepare("SELECT parent FROM objectcategories WHERE id $parentCondition");
  $linkid = $stm->executeScalar();

  $backlink = "index.php?page=build&noJavaScript=1&parent=$linkid";
} else {
  $backlink = "index.php?page=char.events";
}

echo "</TABLE>";
echo "<div class=\"centered\">
  <a href=\"$backlink\">
    <img src=\" " . _IMAGES . "/button_back2.gif\" title=\"<CANTR REPLACE NAME=back_to_character>\"/>
  </a>
</div>";
echo "</div>";
