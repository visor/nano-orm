<?php

namespace Module\Orm\Exception;

class InvalidDataSourceConfiguration extends \Module\Orm\Exception {

	public function __construct($key) {
		parent::__construct('Invalid configuration for data source ' . $this->describeValue($key));
	}

}