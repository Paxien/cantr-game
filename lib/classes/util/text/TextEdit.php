<?php

/**
  * @deprecated use class TextFormat instead
  */

class TextEdit {
  public static function getShorterText($text, $length)
  {
    return TextFormat::getShorterText($text, $length);
  }
  
  /**
   * Fix for exploit which made coin names with multiple spaces look the same as these with single space
   */
  public static function getDiscinctHtmlText($text)
  {
    return TextFormat::getDistinctHtmlText($text);
  }
  
}
