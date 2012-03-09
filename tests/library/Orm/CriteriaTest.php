<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_CriteriaTest extends \TestUtils_TestCase {

	public function testBraceOpenShouldAddCreatedCriteriaIntoParts() {
		$criteria = Orm::criteria();
		$child    = $criteria->braceOpen();

		self::assertNotSame($criteria, $child, '->braceOpen should return child object');
		self::assertSame($criteria, self::getObjectProperty($child, 'parent'), 'parent criteria should be saved into child');
		self::assertContains($child, self::getObjectProperty($criteria, 'parts'), 'child criteria should be added into parts');
	}

	public function testBraceCloseShouldThrowExceptionWhenNoParentCriteria() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'No parent criteria');
		Orm::criteria()->braceClose();
	}

	public function testBraceCloseShouldReturnParentCriteria() {
		$criteria = Orm::criteria();
		$child    = $criteria->braceOpen();
		$parent   = $child->braceClose();

		self::assertSame($criteria, $parent);
	}

	public function testMagickCallShouldThrowsExceptionWhenUnknownMethodCalled() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Unknown field: someUnknownField');
		Orm::criteria()->someUnknownField;
	}

	public function testBraceOpenShouldAddNullLogicalOperatorForFirstChild() {
		$criteria = Orm::criteria();
		$criteria->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(1, $logical);
		self::assertNull($logical[0]);
	}

	public function testBraceOpenShouldAddLogicalOperatorWhenMoreThanOneParts() {
		$criteria = Orm::criteria();
		$criteria->braceOpen();
		$criteria->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(2, $logical);
		self::assertNull($logical[0]);
		self::assertEquals(Orm_Criteria::LOGICAL_AND, $logical[1]);
	}

	public function testAddingLogicalOperators() {
		$criteria = Orm::criteria();
		$criteria->braceOpen();
		$criteria->or->braceOpen();
		$criteria->and->braceOpen();

		$logical = self::getObjectProperty($criteria, 'logicals');
		self::assertCount(3, $logical);
		self::assertNull($logical[0]);
		self::assertEquals(Orm_Criteria::LOGICAL_OR, $logical[1]);
		self::assertEquals(Orm_Criteria::LOGICAL_AND, $logical[2]);
	}

	public function testAddingLogicalOperatorShouldThrowExceptionWhenNoParts1() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->or;
	}

	public function testAddingLogicalOperatorShouldThrowExceptionWhenNoParts2() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->braceOpen()->or;
	}

	public function testAddingTwoLogicalOperatorInARowOrOr() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->braceOpen()->braceClose()->or->or;
	}

	public function testAddingTwoLogicalOperatorInARowOrAnd() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->braceOpen()->braceClose()->or->and;
	}

	public function testAddingTwoLogicalOperatorInARowAndOr() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->braceOpen()->braceClose()->and->or;
	}

	public function testAddingTwoLogicalOperatorInARowAndAnd() {
		self::setExpectedException('NanoOrm_Module\Orm_Exception_Criteria', 'Cannot add logical operator now');
		Orm::criteria()->braceOpen()->braceClose()->and->and;
	}

	public function testCompareScalarOperationsShouldBeAddedIntoPartsAsExpression() {
		$operations = array('equals', 'notEquals', 'greaterThan', 'lessThan', 'like', 'notLike', 'isNull', 'isNotNull');
		$criteria   = Orm::criteria()->braceOpen()->braceClose();
		foreach ($operations as $operation) {
			$this->checkCriteriaOperation($criteria, $operation, 'test', 'value', 'NanoOrm_Module\Orm_Criteria_Expression');
		}
	}

	public function testCompareArrayOperationsShouldBeAddedIntoPartsAsExpression() {
		$operations = array('in', 'notIn');
		$criteria   = Orm::criteria()->braceOpen()->braceClose();
		foreach ($operations as $operation) {
			$this->checkCriteriaOperation($criteria, $operation, 'test', array('value'), 'NanoOrm_Module\Orm_Criteria_Expression');
		}
	}

	public function testAddingCustomExpression() {
		$this->checkCriteriaOperation(Orm::criteria()->braceOpen()->braceClose(), 'custom', 'test', array('value'), 'NanoOrm_Module\Orm_Criteria_Custom');
	}

	protected function criteriaToString(Orm_Criteria $criteria) {
		$parts   = self::getObjectProperty($criteria, 'parts');
		$strings = array();
		foreach ($parts as $part) {
			if ($part instanceof Orm_Criteria) {
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

	protected function checkCriteriaOperation(Orm_Criteria $criteria, $method, $first, $second, $className) {
		$counter = self::getObjectProperty($criteria, 'count');
		$criteria->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(Orm_Criteria::LOGICAL_AND, $logicals[$counter - 1]);

		$criteria->or->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(Orm_Criteria::LOGICAL_OR, $logicals[$counter - 1]);

		$criteria->and->$method($first, $second);
		$counter++;

		$parts    = self::getObjectProperty($criteria, 'parts');
		$logicals = self::getObjectProperty($criteria, 'logicals');
		self::assertCount($counter, $parts);
		self::assertCount($counter, $logicals);
		self::assertInstanceOf($className, $parts[$counter - 1]);
		self::assertEquals(Orm_Criteria::LOGICAL_AND, $logicals[$counter - 1]);
	}

}