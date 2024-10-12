<?php

class StorageCapacityExceededException extends CapacityExceededException
{
  public function __construct($message = "", $code = 0, Exception $cause = null) {
    parent::__construct($message, $code, $cause);
  }
} 
