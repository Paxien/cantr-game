<?php

$playerInfo = Request::getInstance()->getPlayer();
if (!$playerInfo->hasAccessTo(AccessConstants::MANAGE_ADVERTISEMENTS)) {
  CError::throwRedirect("player", "You are not authorized to use this page.");
}

  // SANITIZE INPUT
  $id = HTTPContext::getInteger('id');
  $action = $_REQUEST['action'];

  $db = Db::get();

  show_title ("<CANTR REPLACE NAME=title_manage_advertisement>");

  echo "<CENTER><TABLE WIDTH=700><TR><TD>";

  switch ($action) {

  case "edit" :
    $stm = $db->prepare("SELECT * FROM advertisement WHERE id = :id");
    $stm->bindInt("id", $id);
    $stm->execute();
    $advert = $stm->fetchObject();

  case "new" :
    if (empty($advert)) {
      $advert = new stdClass();
    }

    echo "<FORM METHOD=post ACTION=\"index.php?page=manageadvert&action=store\">";
    echo "<TABLE>";
    echo "<TR><TD><CANTR REPLACE NAME=form_website>:</TD>";
    echo "<TD><INPUT TYPE=text NAME=website VALUE=\"$advert->website\"></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_description>:</TD>";
    echo "<TD><TEXTAREA NAME=description COLS=60 ROWS=5>$advert->description</TEXTAREA></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_contact_email>:</TD>";
    echo "<TD><INPUT TYPE=text NAME=contact VALUE=\"$advert->contact\"></TD></TR>";
    echo "<TR><TD><CANTR REPLACE NAME=form_comments>:</TD>";
    echo "<TD><TEXTAREA NAME=notes COLS=60 ROWS=8>$advert->notes</TEXTAREA></TD></TR>";
    echo "</TABLE>";
    echo "<INPUT TYPE=hidden NAME=id VALUE='$id'>";
    echo "<BR><BR><INPUT TYPE=submit VALUE=\"<CANTR REPLACE NAME=button_store>\">";
    echo "</FORM>";
    break;

  case "store" :
    $website = HTTPContext::getRawString("website");
    $description = HTTPContext::getRawString("description");
    $contact = HTTPContext::getRawString("contact");
    $notes = HTTPContext::getRawString("notes");

    $stm = $db->prepare("SELECT COUNT(*) FROM advertisement WHERE id = :id");
    $stm->bindInt("id", $id);
    $count = $stm->executeScalar();

    $playerInfo = Request::getInstance()->getPlayer();
    $date = date ( "Y-m-d" );
    $author = "{$playerInfo->getFullName()} <{$playerInfo->getEmail()}>";

    if (!$count) {
      $stm = $db->prepare("INSERT INTO advertisement (website, description, contact, notes, date, author)
        VALUES (:website, :description, :contact, :notes, :date, :author)");
      $stm->execute([
        "website" => $website, "description" => $description, "contact" => $contact,
        "notes" => $notes, "date" => $date, "author" => $author,
      ]);
    } else {
      $stm = $db->prepare("UPDATE advertisement SET website = :website, description = :description, contact = :contact,
      notes = :notes, date = :date, author = :author WHERE id = :id");
      $stm->execute([
        "website" => $website, "description" => $description, "contact" => $contact,
        "notes" => $notes, "date" => $date, "author" => $author, "id" => $id,
      ]);
    }

  default :

    echo "<TABLE>";
    echo "<TR><TD><FONT COLOR=yellow>Advertisement campaign</FONT>&nbsp;&nbsp;&nbsp;</TD><TD><FONT COLOR=yellow>Number of referrals</FONT>&nbsp;&nbsp;&nbsp</TD><TD><FONT COLOR=yellow>Suspected signups</FONT></TD><TD><FONT COLOR=yellow>Convergence rate</FONT></TD></TR>";
    $stm = $db->query("SELECT reference, COUNT(id) AS cnt, SUM(suspect_signup) AS signup FROM track_referrals GROUP BY reference");
    foreach ($stm->fetchAll() as $referral) {
      echo "<TR><TD>$referral->reference</TD><TD ALIGN=right>$referral->cnt</TD><TD ALIGN=right>$referral->signup</TD><TD ALIGN=right>" . ($referral->cnt > 0 ? floor($referral->signup * 100 / $referral->cnt) : 0) . " %</TD></TR>";
    }
    echo "</TABLE>";

    show_title("OLD LIST OF ADVERTISEMENT LOCATIONS");
    
    echo "<TABLE>";
    $stm = $db->query("SELECT * FROM advertisement ORDER BY date, website");
    foreach ($stm->fetchAll() as $advert) {
      echo "<TR>";
      echo "<TD><A HREF=\"$advert->website\" TARGET=_blank>$advert->website</A></TD>";
      echo "<TD WIDTH=95>$advert->date</TD>";
      echo "<TD>$advert->author</TD>";
      echo "<TD><A HREF=\"index.php?page=manageadvert&id=$advert->id&action=edit\">[<CANTR REPLACE NAME=button_view_edit>]</A></TD>";
      echo "</TR>";
    }
    echo "</TABLE>";
  }

  echo "<CENTER>";
  echo "<BR><BR><A HREF=\"index.php?page=manageadvert&action=new\"><CANTR REPLACE NAME=page_manage_advert_new></A>";
  echo "<BR><BR><A HREF=\"index.php?page=player\"><CANTR REPLACE NAME=back_to_player></A>";
  echo "</CENTER>";

  echo "</TD></TR></TABLE></CENTER>";

