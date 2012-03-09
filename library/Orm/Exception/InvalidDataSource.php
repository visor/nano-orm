<?php

namespace NanoOrm_Module;

class Orm_Exception_InvalidDataSource extends Orm_Exception {

	public function __construct($source) {
		parent::__construct('Invalid DataSource ' . $this->describeValue($source));
	}

}