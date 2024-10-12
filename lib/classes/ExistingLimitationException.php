<?php

class ExistingLimitationException extends Exception {

  /** @var  GameDate */
  private $timeLeft;
  
  public function __construct($message = "", $code = 0, Exception $cause = null) {
    parent::__construct($message, $code);
  }

  public function setTimeLeft(GameDate $timeLeft)
  {
    $this->timeLeft = $timeLeft;
  }

  public function getTimeLeft()
  {
    return $this->timeLeft;
  }
}
