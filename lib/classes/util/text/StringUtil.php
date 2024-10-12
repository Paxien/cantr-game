<?php

class StringUtil
{
  public static function contains($haystack, $needle)
  {
    return strpos($haystack, $needle) !== false;
  }

  public static function startsWith($haystack, $needle)
  {
    return substr($haystack, 0, strlen($needle)) === $needle;
  }

  public static function endsWith($haystack, $needle)
  {
    return substr($haystack, -strlen($needle)) === $needle;
  }

  /**
   * Find the length of the longest prefix which is the same for both input strings: $a and $b.
   * @param $a string
   * @param $b string
   * @return int the length of the longest common prefix
   */
  public static function commonPrefixLength($a, $b)
  {
    for ($i = 0; $i < mb_strlen($a) && $i < mb_strlen($b); $i++) {
      if ($a[$i] !== $b[$i]) {
        return $i;
      }
    }
    return min(mb_strlen($a), mb_strlen($b));
  }
}
