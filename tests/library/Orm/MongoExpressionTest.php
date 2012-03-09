<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_MongoExpressionTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_Mapper
	 */
	protected $mapper;

	/**
	 * @var Orm_DataSource_Mongo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/AddressMongo.php');

		$this->mapper = new Mapper_Library_Orm_Example_AddressMongo();
		$this->source = new Orm_DataSource_Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));

		Orm::clearSources();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');
	}

	public function testBinaryOperationsShouldParsedWithBothOperands() {
		$values = array(
			array(array('location' => 'b'),                        Orm::criteria()->equals('location', 'b'))
			, array(array('location' => array('$ne' => 'b')),      Orm::criteria()->notEquals('location', 'b'))
			, array(array('location' => array('$gt' => 'b')),      Orm::criteria()->greaterThan('location', 'b'))
			, array(array('location' => array('$lt' => 'b')),      Orm::criteria()->lessThan('location', 'b'))
			, array(array('location' => '/^b$/i'),                 Orm::criteria()->like('location', 'b'))
			, array(array('location' => array('$ne' => '/^b$/i')), Orm::criteria()->notLike('location', 'b'))
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testSameOperationValuesShouldBeStoredInSameArray() {
		$values = array(
			array(
				array('$and' => array('location' => array('b', 'a', 'c'))),
				Orm::criteria()->equals('location', 'b')->equals('location', 'a')->equals('location', 'c')
			)
			, array(
				array('$and' => array('location' => array('$ne' => array('b', 'a', 'c'))))
				, Orm::criteria()->notEquals('location', 'b')->notEquals('location', 'a')->notEquals('location', 'c')
			)
			, array(
				array('$and' => array('location' => array('$in' => array('b', 'a', 'c', 'd'))))
				, Orm::criteria()->in('location', array('b', 'a'))->in('location', array('c', 'd'))
			)
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testUnaryOperationsShoildParsedWithFirstOperandOnly() {
		$values = array(
			array(
				array('location' => array('$type' => 10))
				, Orm::criteria()->isNull('location')
			)
			, array(
				array('location' => array('$exists' => true, '$ne' => null))
				, Orm::criteria()->isNotNull('location')
			)
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testArrayOperations() {
		$values = array(
			array(array('location' => array('$in' => array('1', '2'))),    Orm::criteria()->in('location', array(1, 2)))
			, array(array('location' => array('$nin' => array('2', '3'))), Orm::criteria()->notIn('location', array(2, 3)))
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testArrayOperationsShouldThrowExceptionForNotArrayValues() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Value should be an array');
		$this->source->criteriaToExpression($this->mapper->getResource(), Orm::criteria()->in('location', '1, 2'));
	}

	public function testCustomOperationsShouldParsedAsIs() {
		$expected = array('location' => array('$size' => 1));
		$actual   = $this->source->criteriaToExpression(
			$this->mapper->getResource()
			, Orm::criteria()->custom(array('location' => array('$size' => 1)))
		);
		self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
	}

	public function testLogicalOperatorsInsideOneCriteria() {
		$values = array(
			array(
				array('$and' => array('location' => array('1', '2', '3')))
				, Orm::criteria()->equals('location', 1)->equals('location', 2)->equals('location', 3)
			)
			, array(
				array('$or' => array('location' => array('1', '2', '3')))
				, Orm::criteria()->equals('location', 1)->or->equals('location', 2)->or->equals('location', 3)
			)
			, array(
				array('$or' => array('$size' => 1, '$type' => 1))
				, Orm::criteria()->custom(array('$size' => 1))->or->custom(array('$type' => 1))
			)
			, array(
				array(
					'$or' => array(
						'$and' =>array(
							'$or' => array('location' => array(1, 2))
							, 'location' => 3
						)
						, 'location' => 4
					)
				),
				Orm::criteria()->equals('location', 1)->or->equals('location', 2)->and->equals('location', 3)->or->equals('location', 4)
			)
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testLogicalOperatorsWithSeveralCriterias() {
		self::markTestIncomplete('Not implemented yet');
		$values = array(
			array(
				'(a) and (b)'
				, Orm::criteria()->braceOpen()
					->custom('a')
				->braceClose()
				->and->braceOpen()
					->custom('b')
				->braceClose()
			)
			, array(
				'a or (b and (c or d) and (e or f))'
				, Orm::criteria()->custom('a')->or->braceOpen()
					->custom('b')->and->braceOpen()
						->custom('c')->or->custom('d')
					->braceClose()->and->braceOpen()
						->custom('e')->or->custom('f')
					->braceClose()
				->braceClose()
			)
		);
		foreach ($values as $value) {
			/** @var Orm_Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	protected function tearDown() {
		unSet($this->source);
		Orm::clearSources();
	}

}