<?php

class Validation
{
  public static function isPositiveInt($value)
  {
    return preg_match("/^[1-9][0-9]*$/", $value);
  }

  public static function isPositiveIntArray($values)
  {
    if (!is_array($values)) {
      return false;
    }

    foreach ($values as $value) {
      if (!self::isPositiveInt($value)) {
        return false;
      }
    }
    return true;
  }

  public static function isNonNegativeIntArray($values)
  {
    foreach ($values as $value) {
      if (!self::isNonNegativeInt($value)) {
        return false;
      }
    }
    return true;
  }

  public static function isNegativeInt($value)
  {
    return preg_match("/^-[1-9][0-9]*$/", $value);
  }

  public static function isNonNegativeInt($value)
  {
    return preg_match("/^(0|[1-9][0-9]*)$/", $value);
  }

  public static function isInt($value)
  {
    return preg_match("/^(0|-?[1-9][0-9]*)$/", $value);
  }

  public static function isOnlyBasicCharacterSet($text)
  {
    $set = "a-zA-Z0-9"; // alphanumeric
    $set .= " !@#$%\\^&*()\-=[\];',.\/_+\\\\|{}:\"<>?`~»«"; // allowed special characters
    $set .= "àÀâÂæÆçÇéÉèÈêÊëËïÏîÎôÔœŒùÙûÛüÜÿŸ"; // french
    $set .= "äÄöÖüÜß"; // german
    $set .= "ááÁéÉíÍñÑóÓúÚüÜ¿¡"; // spanish
    $set .= "äÄåÅéÉöÖ"; // swedish
    $set .= "ąĄćĆęĘłŁńŃóÓśŚżŻźŹ"; // polish
    $set .= "çÇğĞıIİöÖşŞüÜ"; // turkish
    $set .= "ãÃáÁàÀâÂçÇéÉêÊíÍõÕóÓôÔúÚüÜ"; // portuguese
    $set .= "ąĄčČęĘėĖįĮšŠųŲūŪžŽ"; // lithuanian
    $set .= "äÄåÅöÖä"; // finnish
    $set .= "àÀèÈéÉìÌòÒóÓùÙ"; // italian

    // russian and bulgarian not included, because it has letters identical to latin ones
    return preg_match("/^[" . $set . "]+$/u", $text);
  }

  public static function isOnlyAlphabeticOrSpace($text, $additionalChars = "")
  {
    $set = "a-zA-Z"; // alphanumeric
    $set .= " "; // only space
    $set .= "àÀâÂæÆçÇéÉèÈêÊëËïÏîÎôÔœŒùÙûÛüÜÿŸ"; // french
    $set .= "äÄöÖüÜß"; // german
    $set .= "ááÁéÉíÍñÑóÓúÚüÜ¿¡"; // spanish
    $set .= "äÄåÅéÉöÖ"; // swedish
    $set .= "ąĄćĆęĘłŁńŃóÓśŚżŻźŹ"; // polish
    $set .= "çÇğĞıIİöÖşŞüÜ"; // turkish
    $set .= "ãÃáÁàÀâÂçÇéÉêÊíÍõÕóÓôÔúÚüÜ"; // portuguese
    $set .= "ąĄčČęĘėĖįĮšŠųŲūŪžŽ"; // lithuanian
    $set .= "äÄåÅöÖä"; // finnish
    $set .= "àÀèÈéÉìÌòÒóÓùÙ"; // italian
    $set .= $additionalChars; // user-specified character set

    // russian and bulgarian not included, because it has letters identical to latin ones
    return preg_match("/^[" . $set . "]+$/u", $text);
  }

  public static function inRange($val, array $range)
  {
    list($min, $max) = $range;
    return ($val >= $min) && ($val <= $max);
  }

  public static function hasCharactersToDescribeActions($text)
  {
    return (bool)preg_match("/[\"'#*()~-]/", $text);
  }

  public static function isEmailValid($string)
  {
    return (bool)preg_match("/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i", $string);
  }
}
