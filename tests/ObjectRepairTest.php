<?php

namespace App\Tests;

use ObjectType;
use PHPUnit\Framework\TestCase;

class ObjectRepairTest extends TestCase
{
  public function testAllWaysToProduceHaveTheSameRequirementsIsTrue()
  {
    $this->assertTrue(ObjectType::allWaysToProduceHaveSameRawRequirements([
      ["stone" => 100, "wood" => 30],
      ["wood" => 30, "stone" => 100],
    ]));
  }

  public function testAllWaysToProduceHaveTheSameRequirementsHasDifferentAmount()
  {
    $this->assertFalse(ObjectType::allWaysToProduceHaveSameRawRequirements([
      ["stone" => 100, "wood" => 30],
      ["stone" => 100, "wood" => 60],
    ]));
  }

  public function testAllWaysToProduceHaveTheSameRequirementsHasDifferentRaw()
  {
    $this->assertFalse(ObjectType::allWaysToProduceHaveSameRawRequirements([
      ["iron" => 100, "wood" => 30],
      ["stone" => 100, "wood" => 30],
    ]));
  }
}