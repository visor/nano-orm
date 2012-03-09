<?php

namespace NanoOrm_Module;

class Orm_DataSource_Expression_Pdo extends Orm_DataSource_Expression {

	private static $operations = array(
		Orm_Criteria::OP_EQUALS         => '='
		, Orm_Criteria::OP_NOT_EQUALS   => '!='
		, Orm_Criteria::OP_GREATER_THAN => '>'
		, Orm_Criteria::OP_LESS_THAN    => '<'
		, Orm_Criteria::OP_IN           => 'in'
		, Orm_Criteria::OP_NOT_IN       => 'not in'
		, Orm_Criteria::OP_LIKE         => 'like'
		, Orm_Criteria::OP_NOT_LIKE     => 'not like'
		, Orm_Criteria::OP_IS_NULL      => 'is null'
		, Orm_Criteria::OP_IS_NOT_NULL  => 'is not null'
	);

	/**
	 * @return string
	 * @param Orm_DataSource|Orm_DataSource_Pdo $dataSource
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public static function create(Orm_DataSource $dataSource, Orm_Resource $resource, Orm_Criteria $criteria) {
		$result   = '';
		$logicals = $criteria->logicals();
		$parts    = $criteria->parts();
		foreach ($parts as $index => $part) {
			/** @var Orm_Criteria_Expression|Orm_Criteria_Custom|Orm_Criteria $part*/
			$logical = $logicals[$index];
			if (null !== $logical) {
				$result .= ' ' . $logical . ' ';
			}
			if ($part instanceof Orm_Criteria_Expression) {
				$result .= $dataSource->quoteName($part->field()) . ' ' . self::getOperator($part->operation());
				if (self::isArrayOperator($part->operation())) {
					$result .= ' ' . self::arrayToOperand($dataSource, $resource, $part);
					continue;
				} elseif (self::isBinaryOperator($part->operation())) {
					$result .= ' ' . $dataSource->pdo()->quote($dataSource->castToDataSource($resource, $part->field(), $part->value()));
				}
			} elseif ($part instanceof Orm_Criteria) {
				$result .= '(' . self::create($dataSource, $resource, $part) . ')';
			} elseif ($part instanceof Orm_Criteria_Custom) {
				$result .= $part->value();
			}
		}
		return $result;
	}

	/**
	 * @return srting
	 * @param int $operator
	 */
	protected static function getOperator($operator) {
		return self::$operations[$operator];
	}

	protected static function arrayToOperand(Orm_DataSource_Pdo $dataSource, Orm_Resource $resource, Orm_Criteria_Expression $expression) {
		$data = $expression->value();
		if (is_array($data)) {
			array_walk($data, function(&$element) use ($dataSource, $resource, $expression) {
				/**
				 * @var Orm_DataSource_Pdo $dataSource
				 * @var Orm_Resource $resource
				 * @var Orm_Criteria_Expression $expression
				 */
				$element = $dataSource->pdo()->quote($dataSource->castToDataSource($resource, $expression->field(), $element));
			});
			$data = implode(', ', $data);
		}
		return '(' . $data . ')';
	}

}