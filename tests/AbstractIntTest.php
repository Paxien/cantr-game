<?php

namespace App\Tests;

use Db;
use PHPUnit\Framework\TestCase;

abstract class AbstractIntTest extends TestCase {
  /** @var Db */
  protected $db;

  protected function setUp() {
    $this->db = Db::get();
    $this->db->beginTransaction();
  }

  protected function tearDown()
  {
    $this->db->rollBack();
  }
}

