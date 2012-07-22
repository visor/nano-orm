<?php

namespace Module\Orm\DataSource;

abstract class Expression {

	protected static $unaryOperations = array(
		\Module\Orm\Criteria::OP_IS_NULL
		, \Module\Orm\Criteria::OP_IS_NOT_NULL
	);

	protected static $arrayOperations = array(
		\Module\Orm\Criteria::OP_IN
		, \Module\Orm\Criteria::OP_NOT_IN
	);

	/**
	 * @return string
	 * @param \Module\Orm\DataSource $dataSource
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 *
	 * @throws \RuntimeException
	 */
	public static function create(\Module\Orm\DataSource $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		throw new \RuntimeException('Should be implemented');
	}

	/**
	 * @return boolean
	 * @param int $operator
	 */
	protected static function isBinaryOperator($operator) {
		if (in_array($operator, self::$unaryOperations)) {
			return false;
		}
		return true;
	}

	/**
	 * @return boolean
	 * @param int $operator
	 */
	protected static function isArrayOperator($operator) {
		return in_array($operator, self::$arrayOperations);
	}

}