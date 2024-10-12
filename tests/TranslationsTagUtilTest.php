<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use TagUtil;

class TranslationsTagUtilTest extends TestCase
{
  /**
   * @param string $name
   * @param string $expected
   *
   * @dataProvider providerFormatName
   */
  public function testFormatName($name, $expected)
  {
    $this->assertSame($expected, TagUtil::formatName($name));
  }

  public function providerFormatName()
  {
    return [
      'iron' => ['iron', 'iron'],
      'wool yarn' => ['wool yarn', 'wool_yarn'],
    ];
  }

  /**
   * @param string $name
   * @param string $expected
   *
   * @dataProvider providerGetBuildingTagByName
   */
  public function testGetBuildingTagByName($name, $expected)
  {
    $this->assertSame($expected, TagUtil::getBuildingTagByName($name));
  }

  public function providerGetBuildingTagByName()
  {
    return [
      'marble building' => ['marble building', 'item_marble_building_b'],
    ];
  }

  /**
   * @group disabled
   *
   * Disabled because it relies on getRawNameFromId which queries the database using old API.
   *
   * @param int $id
   * @param string $expected
   *
   * @dataProvider providerGetRawTagById
   */
  public function testGetRawTagById($id, $expected)
  {
    $this->assertSame($expected, TagUtil::getRawTagById($id));
  }

  public function providerGetRawTagById()
  {
    return [
      'iron' => [10, 'raw_iron'],
      'wool yarn' => [95, 'raw_wool_yarn'],
    ];
  }

  /**
   * @param string $name
   * @param string $expected
   *
   * @dataProvider providerGetRawTagByName
   */
  public function testGetRawTagByName($name, $expected)
  {
    $this->assertSame($expected, TagUtil::getRawTagByName($name));
  }

  public function providerGetRawTagByName()
  {
    return [
      'iron' => ['iron', 'raw_iron'],
      'wool yarn' => ['wool yarn', 'raw_wool_yarn'],
    ];
  }
}