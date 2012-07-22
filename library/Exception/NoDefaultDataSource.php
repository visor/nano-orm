<?php

namespace Module\Orm\Exception;

class NoDefaultDataSource extends \Module\Orm\Exception {

	public function __construct() {
		parent::__construct('Default data source not specified but required');
	}

}