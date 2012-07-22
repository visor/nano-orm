<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class CriteriaTest extends \Nano\TestUtils\TestCase {

	public function testBraceOpenShouldAddCreatedCriteriaIntoParts() {
		$criteria = \Module\Orm\Factory::criteria();
		$child    = $criteria->braceOpen();

		self::assertNotSame($criteria, $child, '->braceOpen should return child object');
		self::assertSame($criteria, self::getObjectProperty($child, 'parent'), 'parent criteria should be saved into child');
		self::assertContains($child, self::getObjectProperty($criteria, 'parts'), 'child criteria should be added into parts');
	}

	public function testBraceCloseShouldThrowExceptionWhenNoParentCriteria() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'No parent criteria');
		\Module\Orm\Factory::criteria()->braceClose();
	}

	public function testBraceCloseShouldReturnParentCriteria() {
		$criteria = \Module\Orm\Factory::criteria();
		$child    = $criteria->braceOpen();
		$parent   = $child->braceClose();

		self::assertSame($criteria, $parent);
	}

	public function testMagickCallShouldThrowsExceptionWhenUnknownMethodCalled() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Unknown field: someUnknownField');
		\Module\Orm\Factory::criteria()->someUnknownField;
	}

	public function testBraceOpenShouldAddNullLogicalOperatorForFirstChild() {
		$criteria = \Module\Orm\Factory::criteria();
		$criteria->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(1, $logical);
		self::assertNull($logical[0]);
	}

	public function testBraceOpenShouldAddLogicalOperatorWhenMoreThanOneParts() {
		$criteria = \Module\Orm\Factory::criteria();
		$criteria->braceOpen();
		$criteria->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(2, $logical);
		self::assertNull($logical[0]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_AND, $logical[1]);
	}

	public function testAddingLogicalOperators() {
		$criteria = \Module\Orm\Factory::criteria();
		$criteria->braceOpen();
		$criteria->or->braceOpen();
		$criteria->and->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(3, $logical);
		self::assertNull($logical[0]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_OR, $logical[1]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_AND, $logical[2]);
	}

	public function testAddingLogicalOperatorShouldThrowExceptionWhenNoParts1() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->or;
	}

	public function testAddingLogicalOperatorShouldThrowExceptionWhenNoParts2() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->braceOpen()->or;
	}

	public function testAddingTwoLogicalOperatorInARowOrOr() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->braceOpen()->braceClose()->or->or;
	}

	public function testAddingTwoLogicalOperatorInARowOrAnd() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->braceOpen()->braceClose()->or->and;
	}

	public function testAddingTwoLogicalOperatorInARowAndOr() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->braceOpen()->braceClose()->and->or;
	}

	public function testAddingTwoLogicalOperatorInARowAndAnd() {
		self::setExpectedException('\Module\Orm\Exception\Criteria', 'Cannot add logical operator now');
		\Module\Orm\Factory::criteria()->braceOpen()->braceClose()->and->and;
	}

	public function testCompareScalarOperationsShouldBeAddedIntoPartsAsExpression() {
		$operations = array('equals', 'notEquals', 'greaterThan', 'lessThan', 'like', 'notLike', 'isNull', 'isNotNull');
		$criteria   = \Module\Orm\Factory::criteria()->braceOpen()->braceClose();
		foreach ($operations as $operation) {
			$this->checkCriteriaOperation($criteria, $operation, 'test', 'value', '\Module\Orm\Criteria\Expression');
		}
	}

	public function testCompareArrayOperationsShouldBeAddedIntoPartsAsExpression() {
		$operations = array('in', 'notIn');
		$criteria   = \Module\Orm\Factory::criteria()->braceOpen()->braceClose();
		foreach ($operations as $operation) {
			$this->checkCriteriaOperation($criteria, $operation, 'test', array('value'), '\Module\Orm\Criteria\Expression');
		}
	}

	public function testAddingCustomExpression() {
		$this->checkCriteriaOperation(\Module\Orm\Factory::criteria()->braceOpen()->braceClose(), 'custom', 'test', array('value'), '\Module\Orm\Criteria\Custom');
	}

	protected function criteriaToString(\Module\Orm\Criteria $criteria) {
		$parts   = self::getObjectProperty($criteria, 'parts');
		$strings = array();
		foreach ($parts as $part) {
			if ($part instanceof \Module\Orm\Criteria) {
				$strings[] = $this->criteriaToString($part);
				continue;
			} elseif (is_array($part)) {
				$items = array();
				foreach ($part as $field => $value) {
					$items[] = $field . ' ' . $value['operator'] . $value['operand'];
				}
				$strings[] = implode(', ', $items);
				continue;
			}
			throw new \RuntimeException('Invalid criteria part: ' . var_export($part, true));
		}
		return '(' . implode(' && ', $strings) . ')';
	}

	protected function checkCriteriaOperation(\Module\Orm\Criteria $criteria, $method, $first, $second, $className) {
		$counter = self::getObjectProperty($criteria, 'count');
		$criteria->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_AND, $logicals[$counter - 1]);

		$criteria->or->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_OR, $logicals[$counter - 1]);

		$criteria->and->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(\Module\Orm\Criteria::LOGICAL_AND, $logicals[$counter - 1]);
	}

}