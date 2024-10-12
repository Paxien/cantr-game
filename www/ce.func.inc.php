<?php
  $ceiconwidth = 17;

  $db = Db::get();

  function GetPrivilleges ($player) {
    global $db;
    $stm = $db->prepare("SELECT access FROM ceAccess WHERE player = :playerId");
    $stm->bindInt("playerId", $player);
    $stm->execute();
    $ret ['count'] = $stm->rowCount();
    foreach ($stm->fetchScalars() as $access) {
      $ret[$access] = true;
    }
    return $ret;
  }

  function HasPrivillege ($privilege) {
    global $session, $db;
    $stm = $db->prepare("SELECT 1 FROM ceAccess WHERE player = :playerId AND access = :privilege");
    $stm->bindInt("playerId", $session->player);
    $stm->bindStr("privilege", $privilege);
    if ($stm->rowCount() > 0) {
      return true;
    }
   DoMessage (GetImg ("cead")." access denied");
   return false;
  }

  function GetImg ($name, $type = "gif") {
    global $ceiconwidth;
    return "<img src=\"graphics/ce/$name.$type\" height=$ceiconwidth>";
  }

  function ceLog ($Description = "") {
    global $_REQUEST, $session, $Logged, $db;

    foreach ($_REQUEST as $var => $val) {
      if (substr($var, 0, 10) != 'show_items' && $var != 's' && $var != 'parent'
        && substr($var, 0, 5) != 'phpbb' && $var != 'FRQSTR' && $var != 'style'
        && $var != 'indent') {
        $params .= "$var=$val | ";
      }
    }

    $stm = $db->prepare("INSERT INTO ceLog (player, params, description) VALUES (:playerId, :params, :description)");
    $stm->bindInt("playerId", $session->player);
    $stm->bindStr("params", $params);
    $stm->bindStr("description", $Description);
    $stm->execute();

    $Logged = true;
  }

  function SkipLog () {
    global $Logged;
    $Logged = true;
  }


  function gPlayer ($id) {
    global $db;
    $stm = $db->prepare("SELECT firstname, lastname FROM players WHERE id = :playerId");
    $stm->bindInt("playerId", $id);
    $stm->execute();
    if ($stm->rowCount()) {
      list ($firstname, $lastname) = $stm->fetchObject();
      return "$firstname $lastname ($id)";
    }
    return "unregistered player #$id";
  }


  function gChar ($id) {
    global $db;
    $stm = $db->prepare("SELECT name FROM chars WHERE id = :charId");
    $stm->bindInt("charId", $id);
    $name = $stm->executeScalar();
    return "$name ($id)";
  }


  function gLocation ($id) {
    global $db;
    $stm = $db->prepare("SELECT name FROM locations WHERE id = :locationId");
    $stm->bindInt("locationId", $id);
    $name = $stm->executeScalar();
    if ($name) return "$name ($id)";
    $stm = $db->prepare("SELECT name FROM oldlocnames WHERE id = :locationId");
    $stm->bindInt("locationId", $id);
    $name = $stm->executeScalar();
    if ($name) return "$name ($id)";
    return "Unnamed location $id";
  }

  function gAnimal ($id) {
    global $db;
    $stm = $db->prepare("SELECT name FROM animal_types WHERE id = :id");
    $stm->bindInt("id", $id);
    return $stm->executeScalar();
  }

  function gLang ($id) {
    global $db;
    $stm = $db->prepare("SELECT name FROM languages WHERE id = :id");
    $stm->bindInt("id", $id);
    return $stm->executeScalar();
  }
