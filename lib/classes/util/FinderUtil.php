<?php

class FinderUtil
{

  /**
   * Return list of integers as sql string or empty string if list is empty.
   * @param  string $name table name
   * @param  array $list list of integers or null
   * @return string which can be added to sql query
   */
  public static function intListOrNothing($name, $list)
  {
    if (!empty($list)) {
      return " AND ". $name ." IN (" . implode(", ", $list) . ") ";
    }
    return "";
  }

  public static function exceptIntListOrNothing($name, $list)
  {
    if (!empty($list)) {
      return " AND ". $name ." NOT IN (" . implode(", ", $list) . ") ";
    }
    return "";
  }

  public static function stringListOrNothing($name, $list) {
    $db = Db::get();
    $list = Pipe::from($list)->map(function($i) use ($db) {
      return $db->quote($i);
    })->toArray();

    if (!empty($list)) {
      return " AND " . $name . " IN (" . implode(",", $list) . ")";
    }
    return "";
  }

  /**
   * Return string for bolean check in sql or empty string if $variable is null.
   * @param  string $name table name
   * @param  array $variable true|false|null
   * @return string which can be added to sql query or empty string if $variable === null
   */
  public static function boolOrNothing($name, $variable)
  {
    if ($variable === null) {
      return "";
    }
    return " AND ". $name ." = ". ($variable ? "1" : "0");
  }


  public static function equalsIntOrNothing($name, $value)
  {
    if ($value === null) {
      return "";
    }
    return " AND ". $name . " = ". $value;
  }

  public static function notEqualsIntOrNothing($name, $value)
  {
    if ($value === null) {
      return "";
    }
    return " AND ". $name . " != ". $value;
  }
}
