<?php

class TextFormat
{
  public static function getShorterText($text, $length)
  {
    if (mb_strlen($text, "UTF-8") > $length) {
      return substr( $text, 0, $length )."...";
    } else {
      return $text;
    }
  }

  /**
   * Fix for exploit which made coin names with multiple spaces look the same as these with single space
   */
  public static function getDistinctHtmlText($text)
  {
    return str_replace("  ", " &nbsp;", $text);
  }

  public static function getPercentFromFraction($fraction, $decimalPoints = 0)
  {
    $formatted = number_format($fraction * 100, $decimalPoints);
    if ($fraction < 1.0 && StringUtil::startsWith($formatted, "100")) {
      return "99" . ($decimalPoints == 0 ? "" : "." . str_repeat("9", $decimalPoints));
    }
    return $formatted;
  }

  public static function withoutNewlines($text)
  {
    return str_replace("\n", "", str_replace("\r", "", $text));
  }
}
