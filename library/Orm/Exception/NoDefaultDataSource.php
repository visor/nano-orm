<?php

namespace NanoOrm_Module;

class Orm_Exception_NoDefaultDataSource extends Orm_Exception {

	public function __construct() {
		parent::__construct('Default data source not specified but required');
	}

}