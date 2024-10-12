<?php

class NoKeyToInnerLockException extends Exception {
  private $lock;

  public function __construct($message = "", KeyLock $lock = null) {
    parent::__construct($message);
    $this->lock = $lock;
  }

  /**
   * @return KeyLock
   */
  public function getLock()
  {
    return $this->lock;
  }
}
