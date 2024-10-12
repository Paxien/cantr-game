<?php

class Window
{
  const OBJECTTYPE_WINDOW_ID = 483;

  public static function hasOpenWindow($id)
  {
    $objectToSeeOutside = CObject::locatedIn($id)->hasProperty("EnableSeeingOutside")->find();
    if ($objectToSeeOutside !== null) {
      return $objectToSeeOutside->getSpecifics() == "open";
    }
    return false;
  }
}
