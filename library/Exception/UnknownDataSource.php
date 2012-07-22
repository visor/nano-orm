<?php

namespace Module\Orm\Exception;

class UnknownDataSource extends \Module\Orm\Exception {

	public function __construct($class) {
		parent::__construct('Unknown data source implementation ' . $this->describeValue($class));
	}

}