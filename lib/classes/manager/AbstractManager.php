<?php

abstract class AbstractManager
{
  protected $db;

  public function __construct(Db $db)
  {
    $this->db = $db;
  }
}