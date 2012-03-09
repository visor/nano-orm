<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_PdoExpressionTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo
	 */
	protected $source;

	/**
	 * @var Orm_Mapper
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Address.php');

		$this->source = new Orm_DataSource_Pdo_Mysql(array());
		$this->mapper = new Mapper_Library_Orm_Example_Address();

		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new Orm_DataSource_Pdo_Mysql((array)$config->test);
	}

	public function testBinaryOperationsShouldParsedWithBothOperands() {
		$values = array(
			"`id` = '1'"          => Orm::criteria()->equals('id', '1')
			, "`id` != '1'"       => Orm::criteria()->notEquals('id', '1')
			, "`id` > '1'"        => Orm::criteria()->greaterThan('id', '1')
			, "`id` < '1'"        => Orm::criteria()->lessThan('id', '1')
			, "`id` like '1'"     => Orm::criteria()->like('id', '1')
			, "`id` not like '1'" => Orm::criteria()->notLike('id', '1')
		);
		foreach ($values as $expected => $criteria) { /** @var Orm_Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testUnaryOperationsShoildParsedWithFirstOperandOnly() {
		$values = array(
			"`id` is null"       => Orm::criteria()->isNull('id')
			, "`id` is not null" => Orm::criteria()->isNotNull('id')
		);
		foreach ($values as $expected => $criteria) { /** @var Orm_Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testArrayOperationsShouldImplodeArrays() {
		$values = array(
			"`id` in (1, 2)"           => Orm::criteria()->in('id', '1, 2')
			, "`id` not in (2, 3)"     => Orm::criteria()->notIn('id', '2, 3')
			, "`id` in ('1', '2')"     => Orm::criteria()->in('id', array(1, 2))
			, "`id` not in ('2', '3')" => Orm::criteria()->notIn('id', array(2, 3))
		);
		foreach ($values as $expected => $criteria) { /** @var Orm_Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testCustomOperationsShouldParsedAsIs() {
		self::assertEquals('a b', $this->source->criteriaToExpression($this->mapper->getResource(), Orm::criteria()->custom('a b')));
	}

	public function testLogicalOperatorsInsideOneCriteria() {
		$values = array(
			'a and b'             => Orm::criteria()->custom('a')->and->custom('b')
			, 'a or b'            => Orm::criteria()->custom('a')->or->custom('b')
			, 'a or b and c or d' => Orm::criteria()->custom('a')->or->custom('b')->and->custom('c')->or->custom('d')
		);
		foreach ($values as $expected => $criteria) { /** @var Orm_Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testLogicalOperatorsWithSeveralCriterias() {
		$values = array(
			'(a) and (b)' =>
				Orm::criteria()->braceOpen()
					->custom('a')
				->braceClose()
				->and->braceOpen()
					->custom('b')
				->braceClose()
			, 'a or (b and (c or d) and (e or f))' =>
				Orm::criteria()->custom('a')->or->braceOpen()
					->custom('b')->and->braceOpen()
						->custom('c')->or->custom('d')
					->braceClose()->and->braceOpen()
						->custom('e')->or->custom('f')
					->braceClose()
				->braceClose()
		);
		foreach ($values as $expected => $criteria) { /** @var Orm_Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	protected function tearDown() {
		unSet($this->source, $this->mapper);
	}

}