<?php

namespace Module\Orm\DataSource\Expression;

class Mongo extends \Module\Orm\DataSource\Expression {

	const MONGO_NULL_TYPE = 10;

	private static $operations = array(
		\Module\Orm\Criteria::OP_EQUALS         => ''
		, \Module\Orm\Criteria::OP_NOT_EQUALS   => '$ne'
		, \Module\Orm\Criteria::OP_GREATER_THAN => '$gt'
		, \Module\Orm\Criteria::OP_LESS_THAN    => '$lt'
		, \Module\Orm\Criteria::OP_IN           => '$in'
		, \Module\Orm\Criteria::OP_NOT_IN       => '$nin'
//		, \Module\Orm\Criteria::OP_LIKE         => ''
//		, \Module\Orm\Criteria::OP_NOT_LIKE     => ''
//		, \Module\Orm\Criteria::OP_IS_NULL      => ''
//		, \Module\Orm\Criteria::OP_IS_NOT_NULL  => ''
	);

	private static $logicals = array(
		\Module\Orm\Criteria::LOGICAL_AND  => '$and'
		, \Module\Orm\Criteria::LOGICAL_OR => '$or'
	);

	/**
	 * @return string
	 * @param \Module\Orm\DataSource|\Module\Orm\DataSource\Mongo $dataSource
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public static function create(\Module\Orm\DataSource $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		$result      = array();
		$values      = &$result;
		$lastLogical = null;
		$logicals    = $criteria->logicals();
		$parts       = $criteria->parts();

		foreach ($parts as $index => $part) {
			/** @var \Module\Orm\Criteria\Expression|\Module\Orm\Criteria\Custom|\Module\Orm\Criteria $part*/
			$logical = $logicals[$index];
			if ($logical !== $lastLogical) {
//				if (null !== $lastLogical) {
					$lastLogical = $logical;
					$result      = array(self::$logicals[$logical] => $result);
					$values      = &$result[self::$logicals[$logical]];
//				}
			}
			if ($part instanceof \Module\Orm\Criteria\Expression) {
				self::appendExpressionPart($values, $dataSource, $resource, $part);

//			} elseif ($part instanceof \Module\Orm\Criteria) {
//				$result .= '(' . self::create($dataSource, $resource, $part) . ')';
			} elseif ($part instanceof \Module\Orm\Criteria\Custom) {
				$values = array_merge($values, $part->value());
			}
		}
		return $result;
	}

	/**
	 * @return array
	 * @param array $operations
	 * @param \Module\Orm\DataSource $dataSource
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria\Expression $part
	 */
	protected static function appendExpressionPart(array &$operations, \Module\Orm\DataSource $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria\Expression $part) {
		$operator = null;
		if (\Module\Orm\Criteria::OP_EQUALS === $part->operation()) {
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
			case \Module\Orm\Criteria::OP_LIKE:
				self::addFieldCondition($operations, $part->field(), null, '/^' . $dataSource->castToDataSource($resource, $part->field(), $part->value()) . '$/i');
				return;

			case \Module\Orm\Criteria::OP_NOT_LIKE:
				self::addFieldCondition($operations, $part->field(), '$ne', '/^' . $dataSource->castToDataSource($resource, $part->field(), $part->value()) . '$/i');
				return;

			case \Module\Orm\Criteria::OP_IS_NULL:
				self::addFieldCondition($operations, $part->field(), '$type', self::MONGO_NULL_TYPE);
				return;

			case \Module\Orm\Criteria::OP_IS_NOT_NULL:
				self::addFieldCondition($operations, $part->field(), '$exists', true);
				self::addFieldCondition($operations, $part->field(), '$ne', null);
				return;

			default:
				throw new \Module\Orm\Exception\Criteria('Unsupported operator: ' . $part->operation());
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
	 * @param \Module\Orm\DataSource $dataSource
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria\Expression $part
	 * @throws \Module\Orm\Exception\Criteria
	 */
	protected static function castArray(\Module\Orm\DataSource $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria\Expression $part) {
		if (is_array($part->value())) {
			$result = array();
			foreach ($part->value() as $item) {
				$result[] = $dataSource->castToDataSource($resource, $part->field(), $item);
			}
			return $result;
		}
		throw new \Module\Orm\Exception\Criteria('Value should be an array');
	}

}