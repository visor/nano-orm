<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class PdoExpressionTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\DataSource\Pdo
	 */
	protected $source;

	/**
	 * @var \Module\Orm\Mapper
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Address.php');

		$this->source = new \Module\Orm\DataSource\Pdo\Mysql(array());
		$this->mapper = new \Module\Orm\Test\Classes\Model\Mapper\Address();

		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new \Module\Orm\DataSource\Pdo\Mysql((array)$config->test);
	}

	public function testBinaryOperationsShouldParsedWithBothOperands() {
		$values = array(
			"`id` = '1'"          => \Module\Orm\Factory::criteria()->equals('id', '1')
			, "`id` != '1'"       => \Module\Orm\Factory::criteria()->notEquals('id', '1')
			, "`id` > '1'"        => \Module\Orm\Factory::criteria()->greaterThan('id', '1')
			, "`id` < '1'"        => \Module\Orm\Factory::criteria()->lessThan('id', '1')
			, "`id` like '1'"     => \Module\Orm\Factory::criteria()->like('id', '1')
			, "`id` not like '1'" => \Module\Orm\Factory::criteria()->notLike('id', '1')
		);
		foreach ($values as $expected => $criteria) { /** @var \Module\Orm\Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testUnaryOperationsShoildParsedWithFirstOperandOnly() {
		$values = array(
			"`id` is null"       => \Module\Orm\Factory::criteria()->isNull('id')
			, "`id` is not null" => \Module\Orm\Factory::criteria()->isNotNull('id')
		);
		foreach ($values as $expected => $criteria) { /** @var \Module\Orm\Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testArrayOperationsShouldImplodeArrays() {
		$values = array(
			"`id` in (1, 2)"           => \Module\Orm\Factory::criteria()->in('id', '1, 2')
			, "`id` not in (2, 3)"     => \Module\Orm\Factory::criteria()->notIn('id', '2, 3')
			, "`id` in ('1', '2')"     => \Module\Orm\Factory::criteria()->in('id', array(1, 2))
			, "`id` not in ('2', '3')" => \Module\Orm\Factory::criteria()->notIn('id', array(2, 3))
		);
		foreach ($values as $expected => $criteria) { /** @var \Module\Orm\Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testCustomOperationsShouldParsedAsIs() {
		self::assertEquals('a b', $this->source->criteriaToExpression($this->mapper->getResource(), \Module\Orm\Factory::criteria()->custom('a b')));
	}

	public function testLogicalOperatorsInsideOneCriteria() {
		$values = array(
			'a and b'             => \Module\Orm\Factory::criteria()->custom('a')->and->custom('b')
			, 'a or b'            => \Module\Orm\Factory::criteria()->custom('a')->or->custom('b')
			, 'a or b and c or d' => \Module\Orm\Factory::criteria()->custom('a')->or->custom('b')->and->custom('c')->or->custom('d')
		);
		foreach ($values as $expected => $criteria) { /** @var \Module\Orm\Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	public function testLogicalOperatorsWithSeveralCriterias() {
		$values = array(
			'(a) and (b)' =>
				\Module\Orm\Factory::criteria()->braceOpen()
					->custom('a')
				->braceClose()
				->and->braceOpen()
					->custom('b')
				->braceClose()
			, 'a or (b and (c or d) and (e or f))' =>
				\Module\Orm\Factory::criteria()->custom('a')->or->braceOpen()
					->custom('b')->and->braceOpen()
						->custom('c')->or->custom('d')
					->braceClose()->and->braceOpen()
						->custom('e')->or->custom('f')
					->braceClose()
				->braceClose()
		);
		foreach ($values as $expected => $criteria) { /** @var \Module\Orm\Criteria $criteria */
			self::assertEquals($expected, $this->source->criteriaToExpression($this->mapper->getResource(), $criteria));
		}
	}

	protected function tearDown() {
		unSet($this->source, $this->mapper);
	}

}