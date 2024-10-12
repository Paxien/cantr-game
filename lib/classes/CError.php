<?php

class CError
{
  var $message;
  var $page;

  function __construct($message = null, $targetPage = 'char')
  {
    $this->message = $message;
    $this->page = $targetPage;
  }

  function report()
  {
    $errorStats = new Statistic("errors", Db::get());
    $errorStats->update($GLOBALS['page'] . " " . mb_substr($this->message, 0, 200), 0);

    $tag = new Tag($this->message);
    $translatedErrorMessage = $tag->interpret();

    if ($GLOBALS['ajaxRequest'] === true) {

      echo json_encode(array("e" => $translatedErrorMessage));
      exit();
    }

    $this->message = urlencode($translatedErrorMessage);

    redirect("$this->page", ["error" => $this->message]);
    exit;
  }

  public static function throwLogout($page, $message)
  {
    Session::deleteCookie();
    self::throwRedirect($page, $message);
  }

  public static function throwRedirectTag($page, $message)
  {
    CError::throwRedirect($page, "<CANTR REPLACE NAME={$message}>");
  }

  public static function throwRedirect($page, $message)
  {
    $Error = new CError ();
    $Error->message = $message;
    $Error->page = $page;
    $Error->report();
    exit ();
  }
}
