<?php

// SANITIZE INPUT
$edit = HTTPContext::getInteger('edit', null);
$content = HTTPContext::getRawString('content', '');
$name = HTTPContext::getRawString('name', '');
$title = HTTPContext::getRawString('title', '');
$post = HTTPContext::getRawString('post', null);

$store = HTTPContext::getRawString('store');
$del = $_REQUEST['del'];

show_title ("STAFF RECRUITMENT FORM");

echo "<div class=\"page\"><table><tr><td>";

$playerInfo = Request::getInstance()->getPlayer();
$admin = $playerInfo->hasAccessTo(AccessConstants::MANAGE_RECRUITMENT_PAGE);

$db = Db::get();
// NOTE: In applicationforms, type = 1: form; type = 2: intro text; type = 3: department info

if (isset($edit) && $admin) {

  $stm = $db->prepare("SELECT title,content FROM applicationforms WHERE type = :type AND name = :name LIMIT 1");
  $stm->bindInt("type", $edit);
  $stm->bindStr("name" ,$name);
  $stm->execute();
  if (!($app_info = $stm->fetchObject())) {
    $app_info = new stdClass();
    $app_info->title = "";
    $app_info->content = "";
  }

  echo "<p><b>Edit page</b>";

  if ($edit == 1) { echo " (application form)"; }
  else if ($edit == 2) { echo " (introduction to recruitment page)"; }
  else if ($edit == 3) { echo " (department info)"; }
  else if ($edit == 0) { echo " (department info - adding new)"; }

  echo "<P><FORM METHOD=post ACTION=\"index.php?page=apply&store=$edit&name=$name\">";
  echo "<TABLE>";
  echo "<TR><TD>Name:</TD><TD>" . ($edit == 0 ? "<INPUT NAME=name SIZE=50>" : "<B>$name</B>") . "</TD></TR>";
  echo "<TR><TD>Title:</TD><TD><INPUT NAME=title SIZE=50 VALUE=\"$app_info->title\"></TD></TR>";
  echo "<TR><TD VALIGN=top>Text:</TD><TD><TEXTAREA NAME=content COLS=60 ROWS=20>" . stripslashes($app_info->content) . "</TEXTAREA></TD></TR>";
  echo "<TR><TD COLSPAN=2><INPUT TYPE=submit VALUE=Store></TD></TR>";
  echo "</TABLE></FORM><BR><BR><BR>";
}

if (isset($store) && $admin) {

  $stm = $db->prepare("SELECT title, content FROM applicationforms WHERE type = :type AND name = :name LIMIT 1");
  $stm->bindInt("type", $store);
  $stm->bindStr("name" ,$name);
  $stm->execute();
  if (!($app_info = $stm->fetchObject())) {

    if ($store == 0) { $store = 3; }

    $stm = $db->prepare("INSERT INTO applicationforms (name, type, title, content) VALUES (:name, :type, :title, :content)");
    $stm->bindStr("name", $name);
    $stm->bindInt("type", $store);
    $stm->bindStr("title", $title);
    $stm->bindStr("content", $content);
    $stm->execute();
  } else {
    $stm = $db->prepare("UPDATE applicationforms SET title = :title, content = :content WHERE type = :type AND name = :name LIMIT 1");
    $stm->bindStr("title", $title);
    $stm->bindStr("content", $content);
    $stm->bindInt("type", $store);
    $stm->bindStr("name", $name);
    $stm->execute();
  }
}

if ($del && $admin) {
  $stm = $db->prepare("DELETE FROM applicationforms WHERE name = :name AND (type = 1 OR type = 3)");
  $stm->bindStr("name", $name);
  $stm->execute();
}

if (isset($post)) {

  $stm = $db->prepare("SELECT content FROM applicationforms WHERE name = :name AND type=1");
  $stm->bindStr("name", $post);
  $stm->execute();
  if ($af = $stm->fetchObject()) {
    $playerInfo = Request::getInstance()->getPlayer();

    $message = "Cantr II Staff Application Form\n";
    $message .= "-------------------------------\n\n";
    $message .= "Position: $post\n";
    $message .= "Applicant: {$playerInfo->getFullName()}\n";
    $message .= "Email: {$playerInfo->getEmail()}\n";
    $message .= "Forum nick:\n";
    $message .= "Discord nick:\n";
    $message .= "Playing since: {$playerInfo->getRegisterDay()}\n\n";

    $message .= $af->content;

    mail ($playerInfo->getEmail(), "Cantr Staff Application Form ($post)", $message, "From: Personnel Officer <".$GLOBALS['emailPersonnel'].">");

    echo "<p>An email has been sent with the application form.";
  } else {

    echo "<p><b>Something went wrong with the application - please try again or contact the Human Resources Department using the web form (follow the \"Contact Cantr II Departments\" link) or send to ".$GLOBALS['emailPersonnel'].").</b>";
  }
}

$stm = $db->query("SELECT content FROM applicationforms WHERE type=2 LIMIT 1");
$intro = $stm->fetchObject();
echo "<P>$intro->content";

if ($admin) { echo " <a href=\"index.php?page=apply&edit=2&name=intro\">[edit]</A>"; }

$stm = $db->query("SELECT name,title,content FROM applicationforms WHERE type=3");
foreach ($stm->fetchAll() as $dep_info) {
  echo "<p><b>$dep_info->title</b>";
  echo "<p>$dep_info->content";
  if ($admin) { echo " <A HREF=\"index.php?page=apply&edit=3&name=$dep_info->name\">[edit]</A> <A HREF=\"index.php?page=apply&del=3&name=$dep_info->name\">[delete]</A>"; }
  echo "<P><A HREF=\"index.php?page=apply&post=$dep_info->name\">Apply for this position</A>";
  if ($admin) { echo " <A HREF=\"index.php?page=apply&edit=1&name=$dep_info->name\">[edit questionnaire]</A>"; }
}

if ($admin) { echo "<P><A HREF=\"index.php?page=apply&edit=0&name=name\">[add item]</A>"; }

echo "<P><div class='centered'><A HREF=\"index.php?page=player\">Back to player page</A></div>";

echo "</td></tr></table></div>";
