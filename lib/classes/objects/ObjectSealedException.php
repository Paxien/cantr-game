<?php

class ObjectSealedException extends Exception
{
  private $sealedObject;

  public function __construct($message, CObject $sealedObject)
  {
    parent::__construct($message);
    $this->sealedObject = $sealedObject;
  }

  /**
   * @return CObject
   */
  public function getObject()
  {
    return $this->sealedObject;
  }
}
