<?php

namespace App\Tests;

use Measure;
use PHPUnit\Framework\TestCase;

class MeasureTest extends TestCase
{
  public function testDistance()
  {
    
    $distance = Measure::distance(5, 3, 2, 7);
    // sqrt((5-2)^2 + (3 - 7)^2) = 5
    $this->assertEquals(5, $distance);

    $distance = Measure::distance(5, -17, -2, 7);
    // sqrt((5 - (-2))^2 + (-17 - 7)^2) = 25
    $this->assertEquals(25, $distance);

    $distance = Measure::distance(0, 0, 0, 0);
    // sqrt((0-0)^2 + (0-0)^2) = 0
    $this->assertEquals(0, $distance);
  }
}
