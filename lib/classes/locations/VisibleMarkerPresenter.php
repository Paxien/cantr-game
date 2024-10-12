<?php

class VisibleMarkerPresenter
{
  /** @var Db */
  private $db;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }

  public function printVisibleObjects($objectsInside)
  {
    foreach ($objectsInside as &$object) {
      $object = "<CANTR OBJNAME ID=$object>";
    }
    $result = "";
    if (count($objectsInside) > 0) {
      $result .= "\n<br><p class=\"sign\" style=\"font-style:italic;font-size:9pt\">";
      $result .= implode(", ", $objectsInside);
      $result .= "</p>\n";
    }
    return $result;
  }

  public function printShipSigns(Location $shipLocation, Character $observer)
  {
    $shipNaming = new LocationNaming($shipLocation, $this->db);
    $allNames = $shipNaming->getAllNames($observer);
    array_shift($allNames);

    $signs = [];
    foreach ($allNames as $name) {
      $signs[] = "<p class=\"sign\">[ $name ]</p>";
    }
    return implode("\n", $signs);
  }

}