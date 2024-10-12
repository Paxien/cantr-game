<?php


class CannotConsumeMultipleRawsException extends Exception
{
  /**
   * @var array where key is raw name and value is amount that was already removed
   */
  private $alreadyRemoved;

  public function __construct($message, array $alreadyRemoved)
  {
    parent::__construct($message);
    $this->alreadyRemoved = $alreadyRemoved;
  }
}