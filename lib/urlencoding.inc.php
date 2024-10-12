<?php

function EncodeURIs($page)
{
  return preg_replace_callback('/index.php\?[^>\"\']*/', 'EncodeURIsCallback', $page);
}

function EncodeURIsCallback($match)
{
  $params = substr($match [0], 10);
  $params = extendParameters($params);

  return "index.php?" . $params;
}

/**
 * Adds "character=$character" to the input string.
 * If input $params contains "noformat=1" then it means the url should not be changed and returns unmodified string.
 *
 * @param string $params input string being URL
 * @return string $params with optionally added $character parameter if no "noformat=1" was in the string
 */
function extendParameters($params)
{
  global $character;

  if (strpos($params, 'noformat=1') !== false
    || strpos($params, "page=player") !== false) { // redirect to player should not contain character data
    return $params;
  }

  if ($character) {
    if (substr($params, 0, -1) != '?') {
      $params .= '&';
    } else {
      Logger::getLogger(__FILE__)->info("no & for params: $params");
    }
    $params .= "character=$character";
  }

  return $params;
}

function DecodeURIs()
{
  foreach ($_REQUEST as $key => $value) {
    $GLOBALS[$key] = $value;
    $_REQUEST[$key] = $value;
    $_GET[$key] = $value;
    $_POST[$key] = $value;
  }
}
