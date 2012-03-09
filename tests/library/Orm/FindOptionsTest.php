<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_FindOptionsTest extends \TestUtils_TestCase {

	public function testCreatingUsingFactoryMethod() {
		self::assertInstanceOf('NanoOrm_Module\Orm_FindOptions', Orm_FindOptions::create());
		self::assertInstanceOf('NanoOrm_Module\Orm_FindOptions', Orm::findOptions());
	}

	public function testLimits() {
		self::assertEquals(1, Orm::findOptions()->limit(1, 2)->getLimitCount());
		self::assertEquals(2, Orm::findOptions()->limit(1, 2)->getLimitOffset());
		self::assertEquals(5, Orm::findOptions()->limitPage(2, 5)->getLimitCount());
		self::assertEquals(10, Orm::findOptions()->limitPage(3, 5)->getLimitOffset());
	}

	public function testOrdering() {
		self::assertEquals(array('field' => true), Orm::findOptions()->orderBy('field')->getOrdering());
		self::assertEquals(array('field' => true), Orm::findOptions()->orderBy('field', true)->getOrdering());
		self::assertEquals(array('field' => true), Orm::findOptions()->orderBy('field', true)->getOrdering());
		self::assertEquals(array('field1' => true, 'field2' => false), Orm::findOptions()->orderBy('field1', true)->orderBy('field2', false)->getOrdering());
		self::assertEquals(array('some criteria' => null), Orm::findOptions()->orderBy('some criteria', null)->getOrdering());
	}

}