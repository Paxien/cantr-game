<?php

/**
 * Class that manages Voting Links. called from page.manage_votinglinks.inc.php
 **/

class VotingLinkManager
{
  /* @var Db */
  private $db;

  /** @var Player */
  private $player;

  function __construct(Player $player)
  {
    $this->player = $player;
    $this->db = Db::get();
  }

  /*
   * return list of votelinks for choose language from database, with param == -1 return all. 
   */
  public function getVoteLinks($language = -1)
  {
    if ($language == -1) {
      $stm = $this->db->query("SELECT * FROM votinglinks ORDER BY `order`");
      return $stm->fetchAll();
    } else {
      $stm = $this->db->prepare("SELECT * FROM votinglinks WHERE language = :language OR language = 0 ORDER BY `order`");
      $stm->bindInt("language", $language);
      $stm->execute();
      return $stm->fetchAll();
    }
  }

  function getLanguages()
  {
    $stm = $this->db->query("SELECT id, name FROM languages");
    return $stm->fetchAll();
  }

  function getLanguageName($language)
  {
    if ($language == 0) {
      return "All";
    }

    $stm = $this->db->prepare("SELECT name FROM languages WHERE id = :id LIMIT 1");
    $stm->bindInt("id", $language);
    return $stm->executeScalar();
  }

  /*
    if some other item has $order like this we need take it up, to give place, recursive.     
  */
  private function doPlaceForReorder($targetOrder, $orderDir)
  {
    $stm = $this->db->prepare("SELECT uid, `order` FROM votinglinks where `order` = :order LIMIT 1");
    $stm->bindInt("order", $targetOrder);
    $stm->execute();
    $existingRow = $stm->fetchObject();

    if ($existingRow) {
      $uid = $existingRow->uid;

      $newOrder = $targetOrder + $orderDir;
      $this->doPlaceForReorder($newOrder, $orderDir);
      $stm = $this->db->prepare("UPDATE votinglinks SET `order` = :newOrder WHERE uid = :uid");
      $stm->bindInt("newOrder", $newOrder);
      $stm->bindInt("uid", $uid);
      $stm->execute();
    }
  }

  function reorder($uid, $orderDir = 0)
  {
    if ($orderDir == 0) {
      return;
    }

    if ($orderDir < 0) {
      $query = "SELECT MAX(`order`) AS ord FROM votinglinks WHERE `order` < (SELECT `order` FROM votinglinks WHERE uid = :uid LIMIT 1 ) LIMIT 1";
    } else {
      $query = "SELECT MIN(`order`) AS ord FROM votinglinks WHERE `order` > (SELECT `order` FROM votinglinks WHERE uid = :uid LIMIT 1 ) LIMIT 1";
    }
    $stm = $this->db->prepare($query);
    $stm->bindInt("uid", $uid);
    $entryOrder = $stm->executeScalar();

    $newOrder = $entryOrder + $orderDir;

    $this->doPlaceForReorder($newOrder, $orderDir);
    $stm = $this->db->prepare("UPDATE votinglinks SET `order` = :newOrder WHERE `uid` = :uid LIMIT 1");
    $stm->bindInt("newOrder", $newOrder);
    $stm->bindInt("uid", $uid);
    $stm->execute();

    $this->log("Reorder of $uid votelink.");
  }

  /*
   * updating existing link, or add new when $id is wrong
   */
  function updateVoteLink($id, $newName, $newUrl, $newLanguage, $enabled)
  {
    $stm = $this->db->prepare("SELECT * FROM votinglinks WHERE uid = :uid LIMIT 1");
    $stm->bindInt("uid", $id);
    $stm->execute();
    $existingRow = $stm->fetchObject();

    $newRow = $this->serializeVoteLink($newUrl, $newName, $newLanguage);

    if (empty($existingRow)) {
      $stm = $this->db->prepare("SELECT MAX(`order`) AS ord FROM votinglinks");
      $maxOrder = $stm->executeScalar();
      $order = $maxOrder + 1;

      $stm = $this->db->prepare("INSERT INTO votinglinks (url, name, language, enabled, `order`) VALUES
        (:newUrl, :newName, :newLanguage, :enabled, :order)");
      $stm->bindStr("newUrl", htmlspecialchars($newUrl));
      $stm->bindStr("newName", $newName);
      $stm->bindInt("newLanguage", $newLanguage);
      $stm->bindInt("enabled", $enabled);
      $stm->bindInt("order", $order);
      $stm->execute();
      return $this->db->lastInsertId();
    } else {
      $stm = $this->db->prepare("UPDATE votinglinks SET url = :newUrl, name = :newName, language = :newLanguage, enabled = :enabled WHERE uid = :uid LIMIT 1");
      $stm->bindStr("newUrl", htmlspecialchars($newUrl)); // todo it shouldn't be escaped before storing in db
      $stm->bindStr("newName", $newName);
      $stm->bindInt("newLanguage", $newLanguage);
      $stm->bindInt("enabled", $enabled);
      $stm->bindInt("uid", $id);
      $stm->execute();

      $oldRow = $this->serializeVoteLink($existingRow->name, $existingRow->url, $existingRow->language);

      $this->log("Voting link $oldRow updated to $newRow.");
      return null;
    }
  }

  function deleteVoteLink($id)
  {
    $stm = $this->db->prepare("SELECT * FROM votinglinks WHERE uid = :uid LIMIT 1");
    $stm->bindInt("uid", $id);
    $stm->execute();
    $old = $stm->fetchObject();
    $oldRow = $this->serializeVoteLink($old->name, $old->url, $old->language);

    $stm = $this->db->prepare("DELETE FROM votinglinks WHERE uid = :uid LIMIT 1");
    $stm->bindInt("uid", $id);
    $stm->execute();
    $this->log("Voting link $oldRow deleted.");
  }

  private function log($logMessage)
  {
    $message = $this->player->getFullNameWithId() . ": " . $logMessage;
    Report::saveInDb("votinglinks", $message);
  }

  private function serializeVoteLink($name, $url, $lang)
  {
    return "\{$name $url $lang\}";
  }
}
