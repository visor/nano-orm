<?php

namespace NanoOrm_Module;

class Orm_Criteria_Custom {

	protected $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function value() {
		return $this->value;
	}

}