<?php

namespace App\Tests;

use Db;
use DeteriorationManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DeteriorationManagerTest extends TestCase
{
  public function testAccumulatedAmountToTaintWhenMaxTaintApplied()
  {
    $stm = $this->getMockBuilder("DbStatement")
      ->disableOriginalConstructor()->getMock();
    $db = $this->getMockBuilder("Db")->disableOriginalConstructor()->getMock();
    $db->method("prepare")->willReturn($stm);
    $stm->method("executeScalar")->willReturn(21000);
      /** @var Db $db */
      $tainted = DeteriorationManager::accumulatedAmountToTaint(400000, "wood", 10, $db);
    $this->assertEquals(15715, $tainted);
  }

  public function testComplexAccumulatedAmountToTaint()
  {
    $stm = $this->getMockBuilder("DbStatement")
      ->disableOriginalConstructor()->getMock();
    $db = $this->getMockBuilder("Db")->disableOriginalConstructor()->getMock();
    $db->method("prepare")->willReturn($stm);
    $stm->method("executeScalar")->willReturn(21000);
    /** @var Db $db */
    $tainted = DeteriorationManager::accumulatedAmountToTaint(400000, "wood", 400, $db);
    $this->assertEquals(319500, $tainted);
  }

  public function testAccumulatedAmountToTaintThrowsWhenDaysIsNotInteger()
  {
    $db = $this->getMockBuilder("Db")->disableOriginalConstructor()->getMock();

    $this->expectException(InvalidArgumentException::class);
    /** @var Db $db */
    DeteriorationManager::accumulatedAmountToTaint(4000, "iron", 33.4, $db);
  }
}
