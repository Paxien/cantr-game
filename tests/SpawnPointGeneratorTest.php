<?php

namespace App\Tests;

use Db;
use PHPUnit\Framework\TestCase;
use SpawnPointGenerator;

class SpawnPointGeneratorTest extends TestCase
{
  private $spawnPointGenerator;

  public function setUp()
  {
    /** @var Db $dbMock */
    $dbMock = $this->getMockBuilder('Db')->disableOriginalConstructor()->getMock();
    $this->spawnPointGenerator = new SpawnPointGenerator([], $dbMock);
  }

  public function testAttractivenessFromActivityFormula()
  {
    $this->assertEquals(1, $this->spawnPointGenerator->attractivenessFromActivity(102, 102));
    $this->assertEquals(1, $this->spawnPointGenerator->attractivenessFromActivity(101, 102));
    $this->assertEquals(0.85, $this->spawnPointGenerator->attractivenessFromActivity(100, 102));
    $this->assertEquals(0.7, $this->spawnPointGenerator->attractivenessFromActivity(99, 102));
    $this->assertEquals(0.55, $this->spawnPointGenerator->attractivenessFromActivity(98, 102));
    $this->assertEquals(0.40, $this->spawnPointGenerator->attractivenessFromActivity(97, 102));
    $this->assertEquals(0.25, $this->spawnPointGenerator->attractivenessFromActivity(96, 102));
    $this->assertEquals(0.1, $this->spawnPointGenerator->attractivenessFromActivity(95, 102));
    $this->assertEquals(0.1, $this->spawnPointGenerator->attractivenessFromActivity(94, 102));
    $this->assertEquals(0.1, $this->spawnPointGenerator->attractivenessFromActivity(null, 102));
  }
}
