<?php

class ProjectSetupTest extends PHPUnit_Framework_TestCase
{
  public function testMassProductionAllowed()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("objects.add:location>11,type>5");
    $objectType->method("getBuildConditions")->willReturn("isnoncentertype:building");

    $this->assertTrue(ProjectSetup::isMassProductionAllowed($objectType));
  }

  public function testMassProductionDisallowedWhenMoreThanOneEntityCreated()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("location.add:region>5;objects.add:location>var>firstid,type>5");
    $objectType->method("getBuildConditions")->willReturn("");

    $this->assertFalse(ProjectSetup::isMassProductionAllowed($objectType));
  }

  public function testMassProductionDisallowedWhenMaxOnObjRestriction()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("location.add:region>5");
    $objectType->method("getBuildConditions")->willReturn("maxonobj:1");

    $this->assertFalse(ProjectSetup::isMassProductionAllowed($objectType));
  }

  public function testMassProductionDisallowedWhenMaxOnLocRestriction()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("location.add:region>5");
    $objectType->method("getBuildConditions")->willReturn("maxonloc:1");

    $this->assertFalse(ProjectSetup::isMassProductionAllowed($objectType));
  }

  public function testMassProductionDisallowedWhenRequiresNameSpecified()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("location.add:name>ask>MyName,region>5");
    $objectType->method("getBuildConditions")->willReturn("");

    $this->assertFalse(ProjectSetup::isMassProductionAllowed($objectType));
  }


  public function testMassProductionDisallowedWhenRequiresUniqueNameSpecified()
  {
    $objectType = $this->getMockBuilder(ObjectType::class)->disableOriginalConstructor()->getMock();
    $objectType->method("getBuildResult")->willReturn("location.add:name>unique-ask>MyName,region>5");
    $objectType->method("getBuildConditions")->willReturn("");

    $this->assertFalse(ProjectSetup::isMassProductionAllowed($objectType));
  }
}
