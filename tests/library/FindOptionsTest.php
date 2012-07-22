<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class FindOptionsTest extends \Nano\TestUtils\TestCase {

	public function testCreatingUsingFactoryMethod() {
		self::assertInstanceOf('\Module\Orm\FindOptions', \Module\Orm\FindOptions::create());
		self::assertInstanceOf('\Module\Orm\FindOptions', \Module\Orm\Factory::findOptions());
	}

	public function testLimits() {
		self::assertEquals(1, \Module\Orm\Factory::findOptions()->limit(1, 2)->getLimitCount());
		self::assertEquals(2, \Module\Orm\Factory::findOptions()->limit(1, 2)->getLimitOffset());
		self::assertEquals(5, \Module\Orm\Factory::findOptions()->limitPage(2, 5)->getLimitCount());
		self::assertEquals(10, \Module\Orm\Factory::findOptions()->limitPage(3, 5)->getLimitOffset());
	}

	public function testOrdering() {
		self::assertEquals(array('field' => true), \Module\Orm\Factory::findOptions()->orderBy('field')->getOrdering());
		self::assertEquals(array('field' => true), \Module\Orm\Factory::findOptions()->orderBy('field', true)->getOrdering());
		self::assertEquals(array('field' => true), \Module\Orm\Factory::findOptions()->orderBy('field', true)->getOrdering());
		self::assertEquals(array('field1' => true, 'field2' => false), \Module\Orm\Factory::findOptions()->orderBy('field1', true)->orderBy('field2', false)->getOrdering());
		self::assertEquals(array('some criteria' => null), \Module\Orm\Factory::findOptions()->orderBy('some criteria', null)->getOrdering());
	}

}