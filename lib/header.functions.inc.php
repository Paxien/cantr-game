<?php

function cantr_redirect($site, $session_id = null)
{
  if ($GLOBALS['ajaxRequest'] === true) {
    return; // do nothing
  }

  $site = EncodeURIs($site);
  header("Location: $site");
}

function redirect($page, $params = [], $subdomain = null, $sessionId = null)
{
  $paramString = "";
  foreach ($params as $name => $value) {
    $paramString .= "&$name=$value";
  }

  $link = "index.php?page=" . $page . $paramString;
  if ($subdomain) {
    $link = $subdomain . "/" . $link;
  }
  cantr_redirect($link, $sessionId);
  exit();
}

/**
 * This function can be used to import class or functions from ./lib directory, not raw pieces of code!
 */
function import_lib($fileName)
{
  require_once(_LIB_LOC . "/" . $fileName);
}

function autoloadClass($className)
{
  $className = strtolower($className);
  import_lib("classes/AutoLoader.php");

  static $classArray = null;
  if (!$classArray) {
    $classArray = AutoLoader::getClassMap();
  }

  $fileName = $classArray[$className];
  if ($fileName !== null) {
    import_lib($fileName);
    return true;
  }

  return false;
}

spl_autoload_register('autoloadClass');

function globalExceptionHandler(Exception $exception)
{
  ob_clean();

  if ($GLOBALS['ajaxRequest']) {
    echo json_encode(["e" => "Unexpected error"]);
  } else {
    $smarty = new CantrSmarty();
    $smarty->displayLang('template.something_was_wrong.tpl');
  }

  $error = sprintf(
    "Uncaught exception '%s' with message '%s' (%d) in %s:%d, %s",
    get_class($exception),
    $exception->getMessage(),
    $exception->getCode(),
    $exception->getFile(),
    $exception->getLine(),
    $exception->getTraceAsString()
  );

  trigger_error($error, E_USER_ERROR);
}

set_exception_handler('globalExceptionHandler');
