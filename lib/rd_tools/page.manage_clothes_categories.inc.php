<?php

// SANITIZE INPUT
$id = HTTPContext::getInteger('id');
$action = $_REQUEST['action'];

show_title("MANAGE CLOTHES CATEGORIES");

$db = Db::get();
/********* CHECKING WHETHER PLAYER HAS ACCESS TO THIS PAGE **************/

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::ALTER_CLOTHING_CATEGORIES)) {
  CError::throwRedirectTag("player", "error_not_authorized");
}

echo "<CENTER><TABLE WIDTH=700>";

switch ($action) {

  case "edit" :

    $stm = $db->prepare("SELECT closCat.*, relCat.name categoryname
                     FROM clothes_categories closCat, objectcategories relCat
                     WHERE relCat.id = :id1 AND closCat.id = :id2");
    $stm->bindInt("id1", $id);
    $stm->bindInt("id2", $id);
    $stm->execute();
    $categories_info = $stm->fetchObject();

  case "new" :

    if ($action == "new") {
      $id = null;
      $hides = array();
      $sortn = 0;
    } else {

      $stm = $db->prepare("SELECT hides, sortn FROM clothes_categories WHERE id = :id");
      $stm->bindInt("id", $id);
      $stm->execute();
      $hides_info = $stm->fetchObject();
      $hides = explode(',', $hides_info->hides);
      $sortn = $hides_info->sortn;
    }

    echo "<TR><TD><TABLE>";

    echo "<FORM METHOD=post ACTION=\"index.php?page=manageclothescategories&action=store\">";
    echo "<INPUT TYPE=hidden NAME=id VALUE=$id>";
    echo "<TR><TD>ID:</TD><TD>$id</TD></TR>";
    echo "<TR><TD>Name:</TD><TD><INPUT TYPE=text NAME=name VALUE=\"$categories_info->categoryname\" SIZE=40></TD>";
    echo "<TR><TD VALIGN=\"top\">Hides:</TD><TD>";

    $stm = $db->prepare("SELECT cc.id,oc.name FROM clothes_categories cc, objectcategories oc " .
      "WHERE cc.id != :id AND oc.id=cc.id");
    $stm->bindInt("id", $id);
    $stm->execute();
    foreach ($stm->fetchAll() as $categories_info) {

      echo "<label><INPUT TYPE=\"checkbox\" NAME=\"hides[]\" VALUE=\"$categories_info->id\"";
      if (in_array($categories_info->id, $hides)) {
        echo " checked";
      }
      echo ">$categories_info->name</label><BR>";
    }

    echo "</TD></TR>";
    echo "<tr><td>List position<br />(0-99):</td>";
    echo "<td><input type='text' name='sortn' value='$sortn'></td></tr>";

    echo "</TABLE></TD></TR>";

    if ($action == "edit") {

      echo "<INPUT TYPE=hidden NAME=edit VALUE=yes>";
    }

    echo "<TR><TD ALIGN=center><BR><INPUT TYPE=submit VALUE=store></TD></TR></FORM>";

    break;

  case "store" :

    $name = HTTPContext::getRawString("name");
    $edit = $_REQUEST['edit'];

    if (!empty($hides)) {
      $hides = implode(',', $hides);
    }

    if ($edit == "yes") {
      $stm = $db->prepare("UPDATE objectcategories SET name = :name WHERE id = :id");
      $stm->execute(["name" => $name, "id" => $id]);

      $stm = $db->prepare("UPDATE clothes_categories SET name = :name, hides = :hides, sortn = :sortn WHERE id = :id");
      $stm->execute(["name" => $name, "hides" => $hides, "sortn" => $sortn, "id" => $id]);
    } else {
      $stm = $db->prepare("INSERT INTO objectcategories (parent, name, status) VALUES (:parent, :name, :status)");
      $stm->execute([
        "parent" => ObjectConstants::OBJCAT_CLOTHES, "name" => $name, "status" => 0,
      ]);
      $id = $db->lastInsertId();

      $stm = $db->prepare("INSERT INTO clothes_categories (id, name,hides,sortn) VALUES (:id, :name, :hides, :sortn)");
      $stm->execute(["id" => $id, "name" => $name, "hides" => $hides, "sortn" => $sortn]);
    }

  case "drop" :

    if ($action == "drop") {
      $stm = $db->prepare("DELETE FROM clothes_categories WHERE id = :id");
      $stm->bindInt("id", $id);
      $stm->execute();
      $stm = $db->prepare("DELETE FROM objectcategories WHERE id = :id");
      $stm->bindInt("id", $id);
      $stm->execute();
    }
}

echo "<TR><TD><TABLE WIDTH=700>";

$stm = $db->query("SELECT cc.id,oc.name,cc.hides FROM clothes_categories cc, objectcategories oc " .
  "WHERE cc.id=oc.id ORDER BY cc.sortn");
foreach ($stm->fetchAll() as $categories_info) {
  echo "<TR><TD>$categories_info->name ($categories_info->id)";
  echo "<A HREF=\"index.php?page=manageclothescategories&action=edit&id=$categories_info->id\"> [edit]</A>";

  echo "<A HREF=\"index.php?page=manageclothescategories&action=drop&id=$categories_info->id\"> [delete]</A>";
  echo "</TD></TR>";
}

echo "</TABLE></TD></TR>";

echo "<TR><TD ALIGN=center><BR><A HREF=\"index.php?page=manageclothescategories&action=new\">Create new clothes category</A></TD></TR>";

echo "<TR><TD ALIGN=center><BR><A HREF=\"index.php?page=player\">Go back to player page</A></TD></TR>";

echo "</TABLE></CENTER>";
