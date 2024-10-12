<?php

namespace App\Tests;

use Connection;
use ConnectionConstants;
use ConnectionType;
use InvalidArgumentException;

/**
 * @group integration
 *
 */
class ConnectionIntTest extends AbstractIntTest
{

  protected function setUp()
  {
    parent::setUp();
    $this->setUpSandRoad();
  }

  public function testTypeIdByName()
  {
    $this->assertEquals(2, Connection::getTypeIdByName("sand_road"));
    try {
      Connection::getTypeIdByName("expressnotexist");
      $this->fail("exception not thrown");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testSpeedFactorByType()
  {
    $this->assertEquals(150, ConnectionType::loadById(2)->getSpeedFactor());
  }

  public function testInst()
  {
    $con = Connection::loadById(500);

    $this->assertEquals(150, $con->getParts()[0]->getType()->getSpeedFactor());

  }

  public function testFailedInst()
  {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("connection of id -404 doesn't exist!");
    Connection::loadById(404);
  }

  public function testTypeByName()
  {
    $this->assertEquals("sand_road", Connection::getTypeNameById(2));

    try {
      Connection::getTypeIdByName("inexistent_road");
      $this->fail("exception not thrown");
    } catch (InvalidArgumentException $e) {
    }

    try {
      Connection::getTypeNameById(404);
      $this->fail("exception not thrown");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testImprovementRaws()
  {
    $con = Connection::loadById(500);
    $raws = $con->getRawsToImproveTo(ConnectionType::loadById(ConnectionConstants::TYPE_PAVED_ROAD));
    $len = $con->getLength();
    $factor = $con->getCostFactor();

    $this->assertEquals($len * $factor * 700, $raws['sand']);
    $this->assertEquals($len * $factor * 500, $raws['stone']);
    $this->assertEquals($len * $factor * 10, $raws['oil']);
    $this->assertCount(3, $raws);

  }

  public function testImprovementDays()
  {

    $con = Connection::loadById(500);
    $days = $con->getDaysToImproveTo(ConnectionType::loadById(ConnectionConstants::TYPE_PAVED_ROAD));
    $len = $con->getLength();
    $factor = $con->getCostFactor();

    $this->assertEquals($len * $factor * 0.7, $days);
  }

  public function testPotentialImprovements()
  {

    $con = Connection::loadById(500);
    $impr = $con->getPotentialImprovements();

    // if sand road can be improved to paved road
    $this->assertContains(ConnectionConstants::TYPE_PAVED_ROAD, $impr);
  }

  public function testIsBeingImproved()
  {
    $con = Connection::loadById(500);

    $canBe = $con->canBeImprovedTo(ConnectionType::loadById(ConnectionConstants::TYPE_PAVED_ROAD));
    $this->assertTrue($canBe);

    $canBe = $con->canBeImprovedTo(ConnectionType::loadById(ConnectionConstants::TYPE_SAND_ROAD));
    $this->assertFalse($canBe);
  }

  public function testGetDirectionFromLocation()
  {
    $con = Connection::loadById(500);

    $dir = $con->getDirectionFromLocation(636);
    $this->assertEquals(300, $dir);

    $dir = $con->getDirectionFromLocation(640);
    $this->assertEquals(120, $dir);

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage("641 is not a valid start/end of connection 500");
    $con->getDirectionFromLocation(641);
  }

  public function testGetOppositeLocation()
  {
    $con = Connection::loadById(500);
    $this->assertEquals(640, $con->getOppositeLocation(636));
    $this->assertEquals(636, $con->getOppositeLocation(640));
    try {
      $con->getOppositeLocation(641);
      $this->fail("exception not thrown");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testGetTypeName()
  {
    $con = Connection::loadById(500);

    $name = $con->getTypeNames()[0];
    $this->assertEquals("sand_road", $name);
  }

  private function setUpSandRoad()
  {
    $this->db->query("INSERT INTO connecttypes (id, name, vehicles, speed_factor, description, speedlimit, improved_from, improve_requirements,
      deter_rate_turn, repair_rate) VALUES (2, 'sand_road', '', 150, '', 300, 1, '', 10, 500)");
    $this->db->query("INSERT INTO connecttypes (id, name, vehicles, speed_factor, description, speedlimit, improved_from, improve_requirements,
      deter_rate_turn, repair_rate) VALUES (7, 'paved_road', '', 200, '', 300, 2, 'raws:sand>700,stone>500,oil>10;days:0.7', 10, 500)");
    $this->db->query("INSERT INTO connections (id, start, end, direction, type, length, improving, start_area, end_area, deterioration)
      VALUES (500, 636, 640, 300, 2, 50, 0, 5, 5, 0)");
    $this->db->query("INSERT INTO connection_parts (connection, part_id, type, deterioration)
      VALUES (500, 1, 2, 0)");
    $this->db->query("INSERT INTO locations (id, name, type, region, area, borders_lake, borders_sea, map, x, y, size, pollution)
      VALUES (636, 'Shai', 1, 0, 1700, 0, 0, 0, 1, 2, 30, 0)");
    $this->db->query("INSERT INTO locations (id, name, type, region, area, borders_lake, borders_sea, map, x, y, size, pollution)
      VALUES (640, 'Pok', 1, 0, 1700, 0, 0, 0, 6, 7, 30, 0)");
  }
}
