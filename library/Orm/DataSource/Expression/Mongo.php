<?php

namespace NanoOrm_Module;

class Orm_DataSource_Expression_Mongo extends Orm_DataSource_Expression {

	const MONGO_NULL_TYPE = 10;

	private static $operations = array(
		Orm_Criteria::OP_EQUALS         => ''
		, Orm_Criteria::OP_NOT_EQUALS   => '$ne'
		, Orm_Criteria::OP_GREATER_THAN => '$gt'
		, Orm_Criteria::OP_LESS_THAN    => '$lt'
		, Orm_Criteria::OP_IN           => '$in'
		, Orm_Criteria::OP_NOT_IN       => '$nin'
//		, Orm_Criteria::OP_LIKE         => ''
//		, Orm_Criteria::OP_NOT_LIKE     => ''
//		, Orm_Criteria::OP_IS_NULL      => ''
//		, Orm_Criteria::OP_IS_NOT_NULL  => ''
	);

	private static $logicals = array(
		Orm_Criteria::LOGICAL_AND  => '$and'
		, Orm_Criteria::LOGICAL_OR => '$or'
	);

	/**
	 * @return string
	 * @param Orm_DataSource|Orm_DataSource_Mongo $dataSource
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public static function create(Orm_DataSource $dataSource, Orm_Resource $resource, Orm_Criteria $criteria) {
		$result      = array();
		$values      = &$result;
		$lastLogical = null;
		$logicals    = $criteria->logicals();
		$parts       = $criteria->parts();

		foreach ($parts as $index => $part) {
			/** @var Orm_Criteria_Expression|Orm_Criteria_Custom|Orm_Criteria $part*/
			$logical = $logicals[$index];
			if ($logical !== $lastLogical) {
//				if (null !== $lastLogical) {
					$lastLogical = $logical;
					$result      = array(self::$logicals[$logical] => $result);
					$values      = &$result[self::$logicals[$logical]];
//				}
			}
			if ($part instanceof Orm_Criteria_Expression) {
				self::appendExpressionPart($values, $dataSource, $resource, $part);

//			} elseif ($part instanceof Orm_Criteria) {
//				$result .= '(' . self::create($dataSource, $resource, $part) . ')';
			} elseif ($part instanceof Orm_Criteria_Custom) {
				$values = array_merge($values, $part->value());
			}
		}
		return $result;
	}

	/**
	 * @return array
	 * @param array $operations
	 * @param Orm_DataSource $dataSource
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria_Expression $part
	 */
	protected static function appendExpressionPart(array &$operations, Orm_DataSource $dataSource, Orm_Resource $resource, Orm_Criteria_Expression $part) {
		$operator = null;
		if (Orm_Criteria::OP_EQUALS === $part->operation()) {
			self::addFieldCondition($operations, $part->field(), null, $dataSource->castToDataSource($resource, $part->field(), $part->value()));
			return;
		}
		if (isSet(self::$operations[$part->operation()])) {
			$operator = self::$operations[$part->operation()];
			if (self::isArrayOperator($part->operation())) {
				self::addFieldCondition($operations, $part->field(), $operator, self::castArray($dataSource, $resource, $part));
				return;
			}
			self::addFieldCondition($operations, $part->field(), $operator, $dataSource->castToDataSource($resource, $part->field(), $part->value()));
			return;
		}
		switch ($part->operation()) {
			case Orm_Criteria::OP_LIKE:
				self::addFieldCondition($operations, $part->field(), null, '/^' . $dataSource->castToDataSource($resource, $part->field(), $part->value()) . '$/i');
				return;

			case Orm_Criteria::OP_NOT_LIKE:
				self::addFieldCondition($operations, $part->field(), '$ne', '/^' . $dataSource->castToDataSource($resource, $part->field(), $part->value()) . '$/i');
				return;

			case Orm_Criteria::OP_IS_NULL:
				self::addFieldCondition($operations, $part->field(), '$type', self::MONGO_NULL_TYPE);
				return;

			case Orm_Criteria::OP_IS_NOT_NULL:
				self::addFieldCondition($operations, $part->field(), '$exists', true);
				self::addFieldCondition($operations, $part->field(), '$ne', null);
				return;

			default:
				throw new Orm_Exception_Criteria('Unsupported operator: ' . $part->operation());
		}
	}

	protected static function addFieldCondition(array &$conditions, $field, $operator, $value) {
		if (isSet($conditions[$field])) {
			if (null === $operator) {
				if (is_array($conditions[$field])) {
					$conditions[$field][] = $value;
				} else {
					$conditions[$field] = array($conditions[$field], $value);
				}
				return;
			}
			if (isSet($conditions[$field][$operator])) {
				if (is_array($conditions[$field][$operator])) {
					if (is_array($value)) {
						$conditions[$field][$operator] = array_merge($conditions[$field][$operator], $value);
					} else {
						$conditions[$field][$operator][] = $value;
					}
				} else {
					if (is_array($value)) {
						$conditions[$field][$operator] = $value;
					} else {
						$conditions[$field][$operator] = array($conditions[$field][$operator], $value);
					}
				}
			} else {
				$conditions[$field][$operator] = $value;
			}
			return;
		}
		if (null === $operator) {
			$conditions[$field] = $value;
			return;
		}
		$conditions[$field] = array($operator => $value);
	}

	/**
	 * @return array
	 * @param Orm_DataSource $dataSource
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria_Expression $part
	 * @throws Orm_Exception_Criteria
	 */
	protected static function castArray(Orm_DataSource $dataSource, Orm_Resource $resource, Orm_Criteria_Expression $part) {
		if (is_array($part->value())) {
			$result = array();
			foreach ($part->value() as $item) {
				$result[] = $dataSource->castToDataSource($resource, $part->field(), $item);
			}
			return $result;
		}
		throw new Orm_Exception_Criteria('Value should be an array');
	}

}