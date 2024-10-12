<?php

/**
* Class handles management of all data from table `descriptions`
*/
class Descriptions {

  const TYPE_BUILDING = 1;
  const TYPE_CHAR = 2;
  const TYPE_OBJECT = 3;

  // max length of descriptions of certain type
  public static $TEXT_MAXLEN = array(
    self::TYPE_BUILDING => 500,
    self::TYPE_CHAR => 1000,
    self::TYPE_OBJECT => 320
  );


  // reported - should change be logged and sent in daily email to PD?
  // row - row name in table `reports`
  // name - type description for report
  public static $REPORT = array(
    self::TYPE_BUILDING => array("reported" => true, "row" => "building_desc", "name" => "building/room description"),
    self::TYPE_CHAR => array("reported" => true, "row" => "desc_changes", "name" => "character public description"),
    self::TYPE_OBJECT => array("reported" => true, "row" => "objects_desc", "name" => "object description")
  );

  /**
  * Returns description of entity based on id (for_id) and type of description. sub_id is optional argument. Chars like "<>" are returned as htmlentities
  */
  public static function getDescription($for_id, $type, $sub_id = null) {
    $sub_text = "";
    if ($sub_id != null) {
      $sub_text = " AND sub_id = " . intval($sub_id);
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT content FROM descriptions WHERE for_id = :forId AND type = :type $sub_text");
    $stm->bindInt("forId", $for_id);
    $stm->bindInt("type", $type);
    return htmlspecialchars($stm->executeScalar());
  }

  /**
  * Loads array of descriptions based on array of their ids. Chars like "<>" are returned as htmlentities
  * @return array key is id, value is description string or null if no description
  */
  public static function getDescriptionsArray(array $ids, $type) {
    $descriptions = array();
    if (!empty($ids)) { // check if there's at least one object we need description for
      foreach ($ids as $val) {
        $descriptions[$val] = null; // not overwritten null will mean no description
      }
      $db = Db::get();
      $stm = $db->prepareWithIntList("SELECT for_id, content FROM descriptions WHERE type = :type AND for_id IN (:ids)", [
        "ids" => $ids,
      ]);
      $stm->bindInt("type", $type);
      $stm->execute();
      foreach ($stm->fetchAll() as $desc) {
        $descriptions[$desc->for_id] = htmlspecialchars($desc->content);
      }
    }
    return $descriptions;
  }

  /**
  * Returns description based on `descriptions` PRIMARY KEY, null if it doesn't exist. Chars like "<>" are returned as htmlentities
  */
  public static function getDescriptionById($desc_id) {
    return htmlspecialchars(self::getRawDescriptionById($desc_id));
  }

  /**
  * Returns description based on `descriptions` PRIMARY KEY, null if it doesn't exist. Characters of description are not escaped.
  */
  public static function getRawDescriptionById($desc_id) {
    $db = Db::get();
    $stm = $db->prepare("SELECT content FROM descriptions WHERE id = :id");
    $stm->bindInt("id", $desc_id);
    return $stm->executeScalar();
  }

  /**
  * Returns character id of author based on `descriptions` PRIMARY KEY, null if it doesn't exist
  */
  public static function getDescriptionAuthorById($desc_id) {
    $db = Db::get();
    $stm = $db->prepare("SELECT author FROM descriptions WHERE id = :id");
    $stm->bindInt("id", $desc_id);
    return $stm->executeScalar();
  }

  /**
  * Creates new description for specified for_id and type. All the data is escaped
  * @return bool true on success
  */
  public static function setDescription($for_id, $type, $content, $author, $sub_id = null) {

    if (!self::isDescriptionAllowed($type, $content)) {
      return false;
    }

    $ery = array("\\r", "\r");
    $content = str_replace( $ery, "", $content);
    if ($sub_id != null) {
      $sub_text_c = ", sub_id = " . intval($sub_id);
      $sub_text_and = "AND sub_id = " . intval($sub_id);
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT content FROM descriptions WHERE for_id = :forId AND type = :type $sub_text_and");
    $stm->bindInt("forId", $for_id);
    $stm->bindInt("type", $type);
    $oldContent = $stm->executeScalar();
    if ($oldContent) { // there already was an entity
      if ($content == null) {
        $stm = $db->prepare("DELETE FROM descriptions WHERE for_id = :forId AND type = :type $sub_text_and LIMIT 1");
        $stm->bindInt("forId", $for_id);
        $stm->bindInt("type", $type);
        $stm->execute();
      } else {
        $stm = $db->prepare("UPDATE descriptions SET content = :content, author = :author $sub_text_c WHERE for_id = :forId AND type = :type $sub_text_and LIMIT 1");
        $stm->bindStr("content", $content);
        $stm->bindInt("author", $author);
        $stm->bindInt("forId", $for_id);
        $stm->bindInt("type", $type);
        $stm->execute();
      }
    } else {
      $stm = $db->prepare("INSERT INTO descriptions SET for_id = :forId, type = :type, content = :content, author = :author $sub_text_and");
      $stm->bindInt("forId", $for_id);
      $stm->bindInt("type", $type);
      $stm->bindStr("content", $content);
      $stm->bindInt("author", $author);
      $stm->execute();
    }

    self::reportDescriptionChange($for_id, $type, $author, $sub_id, $content, $oldContent); // description change for PD report

    return true;
  }

  /**
  * Returns description primary key based on entity of its own id (for_id) and type
  */
  public static function getDescriptionId($for_id, $type, $sub_id = null) {
    if ($sub_id != null){
      $sub_text = " AND sub_id = $sub_id ";
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT id FROM descriptions WHERE for_id = :forId AND type = :type $sub_text");
    $stm->bindInt("forId", $for_id);
    $stm->bindInt("type", $type);
    return $stm->executeScalar();
  }

  /**
  * Returns author based on entity of its own id (for_id) and type
  */
  public static function getDescriptionAuthor($for_id, $type, $sub_id = null) {
    if ($sub_id != null){
      $sub_text = " AND sub_id = $sub_id ";
    }
    $db = Db::get();
    $stm = $db->prepare("SELECT author FROM descriptions WHERE for_id = :forId AND type = :type $sub_text");
    $stm->bindInt("forId", $for_id);
    $stm->bindInt("type", $type);
    return $stm->executeScalar();
  }

  private static function reportDescriptionChange($for_id, $type, $author, $sub_id, $content, $oldContent) {
    if (self::$REPORT[$type]['reported']) { // if change should be reported in PD email
      // report change
      $db = Db::get();
      $stm = $db->prepare("SELECT c.name, p.id, p.firstname, p.lastname
        FROM chars c INNER JOIN players p ON p.id = c.player WHERE c.id = :charId");
      $stm->bindInt("charId", $author);
      $stm->execute();
      list ($charName, $playerId, $firstname, $lastname) = $stm->fetch(PDO::FETCH_NUM);
      $reportText = "Character $charName (chid: $author) of $firstname $lastname (pid: $playerId) changed ". self::$REPORT[$type]["name"] . " for $for_id ";
      $reportText .= self::descriptionDetails($type, $for_id);
      if ($sub_id) $reportText .= "(sub: $sub_id) ";
      $reportText .= "to '$content'\n";
      if ($oldContent) $reportText .= " (was: '$oldContent')";
      Report::saveInDb(self::$REPORT[$type]["row"], $reportText, $GLOBALS['emailPlayers'], self::$REPORT[$type]["name"]);
    }
  }

  private static function descriptionDetails($type, $for_id) {
    $db = Db::get();
    switch($type) {
      case self::TYPE_BUILDING:
        $stm = $db->prepare("SELECT l.name, ot.name FROM locations l
          LEFT JOIN objecttypes ot ON ot.id = l.area WHERE l.id = :locationId");
        $stm->bindInt("locationId", $for_id);
        $stm->execute();
        list ($buildingName, $buildingType) = $stm->fetch(PDO::FETCH_NUM);
        return "('$buildingName' [$buildingType]) ";
      case self::TYPE_CHAR:
        return "";
      case self::TYPE_OBJECT:
        $stm = $db->prepare("SELECT ot.unique_name FROM objects o
          INNER JOIN objecttypes ot ON ot.id = o.type WHERE o.id = :objectId");
        $stm->bindInt("objectId", $for_id);
        $objName = $stm->executeScalar();
        return "($objName) ";
      default:
        return "";
    }
  }

  /**
  * Returns if description doesn't exceed max length
  */
  public static function isDescriptionAllowed( $type, $content ) {
    return (self::$TEXT_MAXLEN[$type] == 0 ) || ( mb_strlen($content, "UTF-8") <= self::$TEXT_MAXLEN[$type] );
  }

}
