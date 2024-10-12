<?php

class DraggingNotCompletedException extends Exception
{
  public function __construct($message, $code = 0, Exception $cause = null)
  {
    parent::__construct($message, $code);
  }
}
