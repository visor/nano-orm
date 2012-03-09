<?php

namespace NanoOrm_Module;

class Orm_Exception_UnknownDataSource extends Orm_Exception {

	public function __construct($class) {
		parent::__construct('Unknown data source implementation ' . $this->describeValue($class));
	}

}