<?php

namespace Module\Orm\Criteria;

class Custom {

	protected $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function value() {
		return $this->value;
	}

}