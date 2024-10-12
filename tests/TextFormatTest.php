<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use TextFormat;

class TextFormatTest extends TestCase
{
  public function testGetPercentFromFractionBelow100WithoutDecimalPoint()
  {
    $this->assertEquals("93", TextFormat::getPercentFromFraction(0.934));
    $this->assertEquals("0", TextFormat::getPercentFromFraction(0.0));
    $this->assertEquals("1", TextFormat::getPercentFromFraction(0.007));
  }

  public function testGetPercentFromFractionBelow100WithDecimalPoint()
  {
    $this->assertEquals("93.4", TextFormat::getPercentFromFraction(0.934, 1));
    $this->assertEquals("0.0", TextFormat::getPercentFromFraction(0.0004, 1));
    $this->assertEquals("0.8", TextFormat::getPercentFromFraction(0.0077, 1));
  }

  public function testGetPercentFromFractionEqualTo100()
  {
    $this->assertEquals("100", TextFormat::getPercentFromFraction(1.0));
    $this->assertEquals("100.0", TextFormat::getPercentFromFraction(1.0, 1));
  }

  public function testGetPercentFromFractionAlmost100()
  {
    $this->assertEquals("99", TextFormat::getPercentFromFraction(0.999));
    $this->assertEquals("99.9", TextFormat::getPercentFromFraction(0.99999999, 1));
    $this->assertEquals("99.9", TextFormat::getPercentFromFraction(0.9994, 1));
  }

  public function testGetPercentFromFractionAbove100()
  {
    $this->assertEquals("101", TextFormat::getPercentFromFraction(1.01));
    $this->assertEquals("100.1", TextFormat::getPercentFromFraction(1.001, 1));
    $this->assertEquals("101.1", TextFormat::getPercentFromFraction(1.011, 1));
    $this->assertEquals("112", TextFormat::getPercentFromFraction(1.123));
    $this->assertEquals("937", TextFormat::getPercentFromFraction(9.37));
  }
}