<?php

namespace NanoOrm_Module;

class Orm_Exception_InvalidDataSourceConfiguration extends Orm_Exception {

	public function __construct($key) {
		parent::__construct('Invalid configuration for data source ' . $this->describeValue($key));
	}

}