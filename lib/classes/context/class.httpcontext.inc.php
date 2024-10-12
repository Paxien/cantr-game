<?php

/**
 * A Class that handles reading input from the front end
 *
 * @author fredrik
 * @since 2010-09-18
 *
 **/
class HTTPContext
{
  /**
   * @desc private constructor, static class
   *
   * @param n /a
   * @return HTTPContext
   */
  private function __construct()
  {
  }

  /**
   * @desc Check if a certain keyword exist
   *
   * @param string keyword
   * @return boolean true if keyword exist
   *
   */
  private static function _keyExist($keyword)
  {
    return isset($_REQUEST[$keyword]);
  }

  /**
   * @desc Retrieve a string by keyword
   * @deprecated string shouldn't be sanitized so early, use it just before query or use prepared statements
   *
   * @param string keyword
   * @param string default value
   * @param boolean strip tags from data
   * @return string the value of the keyword
   */
  public static function getString($keyword, $defaultValue = null, $stripTags = true)
  {
    if (self::_keyExist($keyword)) {
      $data = self::getRawString($keyword);
      if ($stripTags) {
        $data = strip_tags($data);
      }
      return $data;
    }
    return $defaultValue;
  }


  /**
   * @desc Retrieve a string by keyword
   *
   * @param string keyword
   * @param string default value
   * @param boolean strip tags from data
   * @return string the value of the keyword
   */
  public static function getRawString($keyword, $defaultValue = null)
  {
    if (self::_keyExist($keyword)) {
      return $_REQUEST[$keyword];
    }
    return $defaultValue;
  }

  /**
   * @desc Retrieve a string by keyword with stripped HTML tags
   *
   * @param string keyword
   * @param string default value
   * @return string the value of the keyword
   */
  public static function getStripped($a_sKeyWord, $a_sDefaultValue = null)
  {
    if (self::_keyExist($a_sKeyWord)) {
      $data = $_REQUEST[$a_sKeyWord];
      $data = strip_tags($data);
      $data = str_replace(">", "", $data);
      return $data;
    }
    return $a_sDefaultValue;
  }

  /**
   * @desc Retrieve an integer by keyword
   *
   * @param string keyword
   * @param integer default value
   * @return int or default
   */
  public static function getInteger($keyword, $defaultValue = 0)
  {
    if (self::_keyExist($keyword)) {
      return intval($_REQUEST[$keyword]);
    }
    return $defaultValue;
  }

  /**
   * @desc Retrieve a boolean by keyword
   *
   * @param string keyword
   * @param integer default value
   * @return boolean if exists or default value
   */
  public static function getBoolean($keyword, $defaultValue = false)
  {
    if (self::_keyExist($keyword)) {
      $var = $_REQUEST[$keyword];
      if ($var == "true") {
        return true;
      } elseif ($var == "false") {
        return false;
      }

      return !!$var;
    }
    return $defaultValue;
  }

  /**
   * Retrieve an array
   *
   * @param string keyword
   * @param array default value
   * @return array
   **/
  public static function getArray($keyword, $defaultValue = array())
  {
    if (self::_keyExist($keyword) AND is_array($_REQUEST[$keyword])) {
      return (array)$_REQUEST[$keyword];
    }
    return $defaultValue;
  }
}
