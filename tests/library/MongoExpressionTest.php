<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class MongoExpressionTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\Mapper
	 */
	protected $mapper;

	/**
	 * @var \Module\Orm\DataSource\Mongo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/model/AddressMongo.php');
		include_once $this->files->get($this, '/mapper/AddressMongo.php');

		$this->mapper = new \Module\Orm\Test\Classes\Model\Mapper\AddressMongo();
		$this->source = new \Module\Orm\DataSource\Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));

		\Module\Orm\Factory::clearSources();
		\Module\Orm\Factory::addSource('test', $this->source);
		\Module\Orm\Factory::setDefaultSource('test');
	}

	public function testBinaryOperationsShouldParsedWithBothOperands() {
		$values = array(
			array(array('location' => 'b'),                        \Module\Orm\Factory::criteria()->equals('location', 'b'))
			, array(array('location' => array('$ne' => 'b')),      \Module\Orm\Factory::criteria()->notEquals('location', 'b'))
			, array(array('location' => array('$gt' => 'b')),      \Module\Orm\Factory::criteria()->greaterThan('location', 'b'))
			, array(array('location' => array('$lt' => 'b')),      \Module\Orm\Factory::criteria()->lessThan('location', 'b'))
			, array(array('location' => '/^b$/i'),                 \Module\Orm\Factory::criteria()->like('location', 'b'))
			, array(array('location' => array('$ne' => '/^b$/i')), \Module\Orm\Factory::criteria()->notLike('location', 'b'))
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testSameOperationValuesShouldBeStoredInSameArray() {
		$values = array(
			array(
				array('$and' => array('location' => array('b', 'a', 'c'))),
				\Module\Orm\Factory::criteria()->equals('location', 'b')->equals('location', 'a')->equals('location', 'c')
			)
			, array(
				array('$and' => array('location' => array('$ne' => array('b', 'a', 'c'))))
				, \Module\Orm\Factory::criteria()->notEquals('location', 'b')->notEquals('location', 'a')->notEquals('location', 'c')
			)
			, array(
				array('$and' => array('location' => array('$in' => array('b', 'a', 'c', 'd'))))
				, \Module\Orm\Factory::criteria()->in('location', array('b', 'a'))->in('location', array('c', 'd'))
			)
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testUnaryOperationsShoildParsedWithFirstOperandOnly() {
		$values = array(
			array(
				array('location' => array('$type' => 10))
				, \Module\Orm\Factory::criteria()->isNull('location')
			)
			, array(
				array('location' => array('$exists' => true, '$ne' => null))
				, \Module\Orm\Factory::criteria()->isNotNull('location')
			)
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testArrayOperations() {
		$values = array(
			array(array('location' => array('$in' => array('1', '2'))),    \Module\Orm\Factory::criteria()->in('location', array(1, 2)))
			, array(array('location' => array('$nin' => array('2', '3'))), \Module\Orm\Factory::criteria()->notIn('location', array(2, 3)))
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	public function testArrayOperationsShouldThrowExceptionForNotArrayValues() {
		$this->setExpectedException('\Module\Orm\Exception\Criteria', 'Value should be an array');
		$this->source->criteriaToExpression($this->mapper->getResource(), \Module\Orm\Factory::criteria()->in('location', '1, 2'));
	}

	public function testCustomOperationsShouldParsedAsIs() {
		$expected = array('location' => array('$size' => 1));
		$actual   = $this->source->criteriaToExpression(
			$this->mapper->getResource()
			, \Module\Orm\Factory::criteria()->custom(array('location' => array('$size' => 1)))
		);
		self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
	}

	public function testLogicalOperatorsInsideOneCriteria() {
		$values = array(
			array(
				array('$and' => array('location' => array('1', '2', '3')))
				, \Module\Orm\Factory::criteria()->equals('location', 1)->equals('location', 2)->equals('location', 3)
			)
			, array(
				array('$or' => array('location' => array('1', '2', '3')))
				, \Module\Orm\Factory::criteria()->equals('location', 1)->or->equals('location', 2)->or->equals('location', 3)
			)
			, array(
				array('$or' => array('$size' => 1, '$type' => 1))
				, \Module\Orm\Factory::criteria()->custom(array('$size' => 1))->or->custom(array('$type' => 1))
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
				\Module\Orm\Factory::criteria()->equals('location', 1)->or->equals('location', 2)->and->equals('location', 3)->or->equals('location', 4)
			)
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
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
				, \Module\Orm\Factory::criteria()->braceOpen()
					->custom('a')
				->braceClose()
				->and->braceOpen()
					->custom('b')
				->braceClose()
			)
			, array(
				'a or (b and (c or d) and (e or f))'
				, \Module\Orm\Factory::criteria()->custom('a')->or->braceOpen()
					->custom('b')->and->braceOpen()
						->custom('c')->or->custom('d')
					->braceClose()->and->braceOpen()
						->custom('e')->or->custom('f')
					->braceClose()
				->braceClose()
			)
		);
		foreach ($values as $value) {
			/** @var \Module\Orm\Criteria $criteria */
			list($expected, $criteria) = $value;
			$actual = $this->source->criteriaToExpression($this->mapper->getResource(), $criteria);
			self::assertEquals($expected, $actual, var_export($expected, true) . PHP_EOL . var_export($actual, true));
		}
	}

	protected function tearDown() {
		unSet($this->source);
		\Module\Orm\Factory::clearSources();
	}

}