<?php

namespace Module\Orm\Exception;

class InvalidDataSource extends \Module\Orm\Exception {

	public function __construct($source) {
		parent::__construct('Invalid DataSource ' . $this->describeValue($source));
	}

}