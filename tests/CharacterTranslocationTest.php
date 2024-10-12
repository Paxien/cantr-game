<?php

/**
 * It's lame to test such classes...
 */

/** @todo KeyLock mock, namespace */

/**
 * KeyLock stub
 */
class KeyLock {
  private $id;
  public static function loadByLocationId($id) {
    $lock = new self();
    $lock->id = $id;
    return $lock;
  }

  /**
   * @param $charId
   *
   * @throws Exception
   * @return bool
   */
  public function canAccess($charId) {
    if ($charId !== 111) {
      throw new InvalidArgumentException("");
    }

    return $this->id === 2;
  }
}

class CharacterTranslocationTest extends PHPUnit_Framework_TestCase {

  const IS_TRAVELLING_ID = 0;
  const OUTER_LOCKED_ID = 1; // outer location with lock for which CHAR_ID doesn't have a key
  const INNER_OPEN_ID = 2; // inner location without a lock
  const INNER_LOCKED_ID = 3;
  const FAR_AWAY_ID = 777;
  const CHAR_ID = 111;
  /**
   * @var Character
   */
  private $char;

  // Location mocks
  /**
   * @var Location
   */
  private $outerLocked;
  private $innerOpen;
  private $innerLocked;
  private $farAway;
  private $isTravelling;

  public function setUp()
  {
    $this->char = $this->getMockBuilder("Character")->disableOriginalConstructor()->getMock();
    $this->char->method("getId")->willReturn(self::CHAR_ID);
    $this->char->method("getTotalWeight")->willReturn(70000);
    
    $this->outerLocked = $this->getMockBuilder("Location")->disableOriginalConstructor()->getMock();
    $this->outerLocked->method("getMaxWeight")->willReturn(100000);
    $this->outerLocked->method("getMaxCharacters")->willReturn(10);
    $this->outerLocked->method("getCharacterCount")->willReturn(1);
    $this->innerOpen = clone $this->outerLocked;
    $this->innerLocked = clone $this->outerLocked;
    $this->farAway = clone $this->outerLocked;
    $this->isTravelling = clone $this->outerLocked;
    
    $this->outerLocked->method("getId")->willReturn(self::OUTER_LOCKED_ID);
    $this->outerLocked->method("getRegion")->willReturn(12345);
    $this->outerLocked->method("getTotalWeight")->willReturn(0);

    $this->innerOpen->method("getId")->willReturn(self::INNER_OPEN_ID);
    $this->innerOpen->method("getRegion")->willReturn(self::OUTER_LOCKED_ID);
    $this->innerOpen->method("getTotalWeight")->willReturn(1000);

    $this->innerLocked->method("getId")->willReturn(self::INNER_LOCKED_ID);
    $this->innerLocked->method("getRegion")->willReturn(self::OUTER_LOCKED_ID);
    $this->innerLocked->method("getTotalWeight")->willReturn(200000);

    $this->farAway->method("getRegion")->willReturn(self::FAR_AWAY_ID);

    $this->isTravelling->method("getRegion")->willReturn(self::IS_TRAVELLING_ID);

  }

  /**
   * @throws Exception
   */
  public function testSimpleTranslocation()
  {
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $this->char->expects($this->once())->method("setLocation")->with(self::INNER_OPEN_ID);
    
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->innerOpen);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testInnerLocked()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->innerLocked);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $this->expectException(NoKeyToInnerLockException::class);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testOuterLocked()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->innerOpen);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $trans->setCheckOuterLock(true); // happens for docked boats
    $this->expectException(NoKeyToOuterLockException::class);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testCapacityExceeded()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->innerLocked);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $trans->setCheckInnerLock(false);
    $this->expectException(CapacityExceededException::class);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testTooFarAway()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->farAway);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $this->expectException(TooFarAwayException::class);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testToIsTravelling()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->isTravelling);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::OUTER_LOCKED_ID);
    $this->expectException(TooFarAwayException::class);
    $trans->perform();
  }

  /**
   * @throws Exception
   */
  public function testBadInitialLoc()
  {
    $trans = new CharacterTranslocation($this->char, $this->outerLocked, $this->innerOpen);
    $this->char->expects($this->once())->method("getLocation")->willReturn(self::INNER_LOCKED_ID);
    $this->expectException(BadInitialLocationException::class);
    $trans->perform();
  }

}
