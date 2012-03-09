<?php

namespace NanoOrm_Module;

/**
 * @method Orm_Criteria and()
 * @method Orm_Criteria or()
 *
 * @property Orm_Criteria and
 * @property Orm_Criteria or
 */
class Orm_Criteria {

	const LOGICAL_OR      = 'or';
	const LOGICAL_AND     = 'and';

	const OP_EQUALS       =  1;
	const OP_NOT_EQUALS   =  2;
	const OP_GREATER_THAN =  3;
	const OP_LESS_THAN    =  4;
	const OP_IN           =  5;
	const OP_NOT_IN       =  6;
	const OP_LIKE         =  7;
	const OP_NOT_LIKE     =  8;
	const OP_IS_NULL      =  9;
	const OP_IS_NOT_NULL  = 10;

	/**
	 * @var int
	 */
	protected $count = 0;

	/**
	 * @var array
	 */
	protected $parts = array();

	/**
	 * @var string[]
	 */
	protected $logicals = array();

	/**
	 * @var Orm_Criteria
	 */
	protected $parent = null;

	/**
	 * @return Orm_Criteria
	 */
	public static function create() {
		return new self();
	}

	/**
	 * @return Orm_Criteria
	 */
	public function braceOpen() {
		$result = new self($this);
		$this->addPart($result);
		return $result;
	}

	/**
	 * @return Orm_Criteria
	 */
	public function braceClose() {
		if (null === $this->parent) {
			throw new Orm_Exception_Criteria('No parent criteria');
		}
		return $this->parent;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function equals($field, $value) {
		$this->addExpression($field, self::OP_EQUALS, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function notEquals($field, $value) {
		$this->addExpression($field, self::OP_NOT_EQUALS, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function greaterThan($field, $value) {
		$this->addExpression($field, self::OP_GREATER_THAN, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function lessThan($field, $value) {
		$this->addExpression($field, self::OP_LESS_THAN, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param array $values
	 */
	public function in($field, $values) {
		$this->addExpression($field, self::OP_IN, $values);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param array $values
	 */
	public function notIn($field, $values) {
		$this->addExpression($field, self::OP_NOT_IN, $values);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function like($field, $value) {
		$this->addExpression($field, self::OP_LIKE, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 * @param string $value
	 */
	public function notLike($field, $value) {
		$this->addExpression($field, self::OP_NOT_LIKE, $value);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 */
	public function isNull($field) {
		$this->addExpression($field, self::OP_IS_NULL, null);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $field
	 */
	public function isNotNull($field) {
		$this->addExpression($field, self::OP_IS_NOT_NULL, null);
		return $this;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $value
	 */
	public function custom($value) {
		$this->addPart(new Orm_Criteria_Custom($value));
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function logicals() {
		return $this->logicals;
	}

	/**
	 * @return array
	 */
	public function parts() {
		return $this->parts;
	}

	/**
	 * @return Orm_Criteria
	 * @param string $method
	 * @throws Orm_Exception_Criteria
	 */
	public function __get($method) {
		if (self::LOGICAL_OR === $method || self::LOGICAL_AND === $method) {
			if (!$this->canAddLogical()) {
				throw new Orm_Exception_Criteria('Cannot add logical operator now');
			}
			$this->addLogical($method);
			return $this;
		}
		throw new Orm_Exception_Criteria('Unknown field: ' . $method);
	}

	/**
	 * @param null|Orm_Criteria $parent
	 */
	protected function __construct(Orm_Criteria $parent = null) {
		$this->parent = $parent;
	}

	protected function canAddLogical() {
		if (0 === $this->count) {
			return false;
		}
		if ($this->count < count($this->logicals)) {
			return false;
		}
		return true;
	}

	protected function addExpression($first, $operator, $second) {
		$this->addPart(new Orm_Criteria_Expression($first, $operator, $second));
	}

	protected function addPart($part) {
		if (0 === $this->count) {
			$this->addLogical(null);
		} elseif (!isSet($this->logicals[$this->count])) {
			$this->addLogical(self::LOGICAL_AND);
		}
		$this->parts[] = $part;
		++$this->count;
	}

	protected function addLogical($value) {
		$this->logicals[] = $value;
	}

}