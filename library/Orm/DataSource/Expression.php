<?php

namespace NanoOrm_Module;

abstract class Orm_DataSource_Expression {

	protected static $unaryOperations = array(
		Orm_Criteria::OP_IS_NULL
		, Orm_Criteria::OP_IS_NOT_NULL
	);

	protected static $arrayOperations = array(
		Orm_Criteria::OP_IN
		, Orm_Criteria::OP_NOT_IN
	);

	/**
	 * @return string
	 * @param Orm_DataSource $dataSource
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 *
	 * @throws \RuntimeException
	 */
	public static function create(Orm_DataSource $dataSource, Orm_Resource $resource, Orm_Criteria $criteria) {
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