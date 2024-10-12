<?php

namespace App\Tests;

use DbObjectRegistry;
use PHPUnit\Framework\TestCase;
use stdClass;

class DbObjectRegistryTest extends TestCase
{
  /** @var DbObjectRegistry */
  public $registry;

  public function setUp()
  {
    $this->registry = new DbObjectRegistry();
  }
  
  public function testPutGet()
  {
    $a = new StdClass();
    $this->registry->put(123, $a);
    // is properly put
    $this->assertTrue($this->registry->contains(123));
    $this->assertFalse($this->registry->contains(0));

    // is properly put and got
    $b = $this->registry->get(123);
    $this->assertSame($a, $b);
  }

  public function testRemove()
  {
    $a = new StdClass();
    $this->registry->put(123, $a);
    
    // is properly removed
    $ret = $this->registry->remove(123);
    $this->assertTrue($ret);
    $this->assertFalse($this->registry->contains(123));
    $c = $this->registry->get(123);
    $this->assertNull($c);

    // try to remove again
    $ret = $this->registry->remove(123);
    $this->assertFalse($ret);
  }
}
