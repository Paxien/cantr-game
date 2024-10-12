<?php

namespace App\Tests;

use Dragging;
use DraggingConstants;
use InvalidArgumentException;

/**
 * @group integration
 */
class DraggingIntTest extends AbstractIntTest
{
  const EX_VICTIM_TYPE = DraggingConstants::TYPE_HUMAN;
  const EX_VICTIM_ID = 100;
  const NONEX_VICTIM_TYPE = DraggingConstants::TYPE_HUMAN;
  const NONEX_VICTIM_ID = 101;

  public function setUpExample()
  {
    $this->db->query("INSERT INTO dragging (id, victim, victimtype, goal)
      VALUES (111, " . self::EX_VICTIM_ID . ", " . self::EX_VICTIM_TYPE . ", 636)");
    $this->db->query("INSERT INTO draggers (dragging_id, dragger)
      VALUES (111, 123), (111, 124)");
  }

  public function testLoadByVictim()
  {
    $this->setUpExample();

    $dragging = Dragging::loadByVictim(self::EX_VICTIM_TYPE, self::EX_VICTIM_ID);
    $this->assertEquals(self::EX_VICTIM_ID, $dragging->getVictim());

    try {
      Dragging::loadByVictim(self::NONEX_VICTIM_TYPE, self::NONEX_VICTIM_ID);
      $this->fail("Didn't throw exception");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testLoadByDragger()
  {
    $this->setUpExample();

    $dragging = Dragging::loadByDragger(123);
    $this->assertContains(123, $dragging->getDraggers());
    $this->assertContains(124, $dragging->getDraggers());
    $this->assertCount(2, $dragging->getDraggers());

    try {
      Dragging::loadByDragger(125);
      $this->fail("Didn't throw exception");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testLoadById()
  {
    $this->setUpExample();

    $dragging = Dragging::loadById(111);
    $this->assertEquals(111, $dragging->getId());
    $this->assertCount(2, $dragging->getDraggers());

    try {
      Dragging::loadById(112);
      $this->fail("Didn't throw exception");
    } catch (InvalidArgumentException $e) {
    }
  }

  public function testNewInstance()
  {
    $this->markTestSkipped();
    try {
      Dragging::newInstance(7, self::EX_VICTIM_ID, 636);
      $this->fail("should throw exception");
    } catch (InvalidArgumentException $e) {
    }

    try {
      Dragging::newInstance(self::EX_VICTIM_TYPE, 0, 7);
      $this->fail("should throw exception");
    } catch (InvalidArgumentException $e) {
    }

    try {
      Dragging::newInstance(self::EX_VICTIM_TYPE, self::EX_VICTIM_ID, -7);
      $this->fail("should throw exception");
    } catch (InvalidArgumentException $e) {
    }

    $newDragging = Dragging::newInstance(self::EX_VICTIM_TYPE, self::EX_VICTIM_ID, 636);
    $this->assertEquals(636, $newDragging->getGoal());

    $newDragging = Dragging::newInstance(
      self::EX_VICTIM_TYPE, self::EX_VICTIM_ID, DraggingConstants::GOAL_OUT_OF_PROJECT);
    $this->assertEquals(DraggingConstants::GOAL_OUT_OF_PROJECT, $newDragging->getGoal());
  }

  private function getDraggingInstance()
  {
    return Dragging::newInstance(self::EX_VICTIM_TYPE, self::EX_VICTIM_ID, 636);
  }

  public function testSaveInDbNewAddRemove()
  {
    $dragging = $this->getDraggingInstance();

    // to see if it's not stored in db
    $this->assertFalse($dragging->hasId());

    // try to save without any dragger, it shouldn't succeed
    $dragging->saveInDb();
    // false = it's still not stored in db
    $this->assertFalse($dragging->hasId());

    $dragging->addDragger(123);
    $dragging->addDragger(124);
    $dragging->saveInDb();
    $this->assertTrue($dragging->hasId());
    $draggingId = $dragging->getId();

    // load the same data
    $dragging = Dragging::loadById($draggingId);
    $this->assertCount(2, $dragging->getDraggers());
    $this->assertContains(123, $dragging->getDraggers());
    $this->assertContains(124, $dragging->getDraggers());

    $dragging->removeDragger(123);
    $this->assertCount(1, $dragging->getDraggers());
    $this->assertContains(124, $dragging->getDraggers());

    // update data
    $dragging->saveInDb();
    $this->assertTrue($dragging->hasId());
  }

  public function testSaveInDbFinish()
  {
    // set up data in db
    $dragging = $this->getDraggingInstance();
    $dragging->addDragger(124);
    $dragging->saveInDb();
    $draggingId = $dragging->getId();

    $dragging = Dragging::loadById($draggingId);

    $dragging->remove();
    $dragging->saveInDb();

    $this->assertFalse($dragging->hasId());

    // make sure it's deleted
    try {
      Dragging::loadById(111);
    } catch (InvalidArgumentException $e) {
    }

  }

  // updating project without any draggers should remove data about dragging
  public function testSaveInDbFinishNoDraggers()
  {
    $this->markTestSkipped();
    // set up data in db
    $dragging = $this->getDraggingInstance();
    $dragging->addDragger(124);
    $dragging->saveInDb();

    // remove last dragger
    $dragging->removeDragger(124);
    $this->assertCount(0, $dragging->getDraggers());

    $this->assertTrue($dragging->hasId());
    // no draggers, so dragging project should be removed
    $dragging->saveInDb();
    $this->assertFalse($dragging->hasId());
    // these two values guarantee this project is marked as removed

    // check if dragging is in db
    try {
      Dragging::loadById(111);
      $this->fail("should throw exception");
    } catch (InvalidArgumentException $e) {
    }

    // try to save again already removed dragging, doesn't change anything
    $dragging->saveInDb();

    $this->assertFalse($dragging->hasId());
  }
}
