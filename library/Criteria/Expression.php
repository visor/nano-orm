<?php

namespace Module\Orm\Criteria;

class Expression {

	protected $first;
	protected $operation;
	protected $second;

	public function __construct($first, $operation, $second) {
		$this->first     = $first;
		$this->operation = $operation;
		$this->second    = $second;
	}

	/**
	 * @return string
	 */
	public function field() {
		return $this->first;
	}

	/**
	 * @return int
	 */
	public function operation() {
		return $this->operation;
	}

	/**
	 * @return mixed
	 */
	public function value() {
		return $this->second;
	}

}