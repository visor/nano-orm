<?php

namespace Module\Orm\DataSource\Expression;

class Pdo extends \Module\Orm\DataSource\Expression {

	private static $operations = array(
		\Module\Orm\Criteria::OP_EQUALS         => '='
		, \Module\Orm\Criteria::OP_NOT_EQUALS   => '!='
		, \Module\Orm\Criteria::OP_GREATER_THAN => '>'
		, \Module\Orm\Criteria::OP_LESS_THAN    => '<'
		, \Module\Orm\Criteria::OP_IN           => 'in'
		, \Module\Orm\Criteria::OP_NOT_IN       => 'not in'
		, \Module\Orm\Criteria::OP_LIKE         => 'like'
		, \Module\Orm\Criteria::OP_NOT_LIKE     => 'not like'
		, \Module\Orm\Criteria::OP_IS_NULL      => 'is null'
		, \Module\Orm\Criteria::OP_IS_NOT_NULL  => 'is not null'
	);

	/**
	 * @return string
	 * @param \Module\Orm\DataSource|\Module\Orm\DataSource\Pdo $dataSource
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public static function create(\Module\Orm\DataSource $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		$result   = '';
		$logicals = $criteria->logicals();
		$parts    = $criteria->parts();
		foreach ($parts as $index => $part) {
			/** @var \Module\Orm\Criteria\Expression|\Module\Orm\Criteria\Custom|\Module\Orm\Criteria $part*/
			$logical = $logicals[$index];
			if (null !== $logical) {
				$result .= ' ' . $logical . ' ';
			}
			if ($part instanceof \Module\Orm\Criteria\Expression) {
				$result .= $dataSource->quoteName($part->field()) . ' ' . self::getOperator($part->operation());
				if (self::isArrayOperator($part->operation())) {
					$result .= ' ' . self::arrayToOperand($dataSource, $resource, $part);
					continue;
				} elseif (self::isBinaryOperator($part->operation())) {
					$result .= ' ' . $dataSource->pdo()->quote($dataSource->castToDataSource($resource, $part->field(), $part->value()));
				}
			} elseif ($part instanceof \Module\Orm\Criteria) {
				$result .= '(' . self::create($dataSource, $resource, $part) . ')';
			} elseif ($part instanceof \Module\Orm\Criteria\Custom) {
				$result .= $part->value();
			}
		}
		return $result;
	}

	/**
	 * @return string
	 * @param int $operator
	 */
	protected static function getOperator($operator) {
		return self::$operations[$operator];
	}

	protected static function arrayToOperand(\Module\Orm\DataSource\Pdo $dataSource, \Module\Orm\Resource $resource, \Module\Orm\Criteria\Expression $expression) {
		$data = $expression->value();
		if (is_array($data)) {
			array_walk($data, function(&$element) use ($dataSource, $resource, $expression) {
				/**
				 * @var \Module\Orm\DataSource\Pdo $dataSource
				 * @var \Module\Orm\Resource $resource
				 * @var \Module\Orm\Criteria\Expression $expression
				 */
				$element = $dataSource->pdo()->quote($dataSource->castToDataSource($resource, $expression->field(), $element));
			});
			$data = implode(', ', $data);
		}
		return '(' . $data . ')';
	}

}