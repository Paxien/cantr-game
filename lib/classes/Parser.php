<?php

// will be expanded for three-level data structures
class Parser
{
  /**
   * Splits data in format like: "buttons:sss>saa>ss;key:value>more" into array of UNIQUE elements
   * where key of single element (between two ";") string before first ":" and value is the rest.
   *
   * Example: "buttons:sss>saa;key:value" (default delimiters) => array ("buttons" => "sss>saa", "key" => "value")
   *
   * @param $input string input text to parse
   * @param string $delims a two-character string of delimiters.
   *                The first is a delimiter between key-value pairs, the second is a delimiter between key and value.
   * @param bool $allowNoValue if true then keys without values
   *                (no second delimiter in a key-value), then it's set as "true", ignored otherwise.
   * @return array with keys and values as in the example. When input is empty ("", null) returns empty array
   */
  public static function rulesToArray($input, $delims = ";:", $allowNoValue = false)
  {
    $data = array();
    if (empty($input)) { // when no data then empty array
      return $data;
    }
    $big_parts = explode($delims[0], $input);

    foreach ($big_parts as $part) {
      $small_parts = explode($delims[1], $part, 2);
      $property_name = $small_parts[0];
      if (isset($small_parts[1])) {
        $data[$property_name] = $small_parts[1];
      } elseif ($allowNoValue) {
        $data[$property_name] = true;
      }
    }
    return $data;
  }

  public static function arrayToRules($inputArray, $delims = ";:")
  {
    $data = array();
    foreach ($inputArray as $key => $value) {
      $data[] = $key . $delims[1] . $value;
    }
    return implode($delims[0], $data);
  }

}
