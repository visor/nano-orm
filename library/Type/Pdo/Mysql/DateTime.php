<?php

namespace Module\Orm\Type\Pdo\Mysql;

class DateTime extends \Module\Orm\Type\Pdo\Date {

	/**
	 * @return string
	 */
	public function format() {
		return 'Y-m-d H:i:s';
	}

}