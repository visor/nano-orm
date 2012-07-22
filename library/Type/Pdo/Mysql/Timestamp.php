<?php

namespace Module\Orm\Type\Pdo\Mysql;

class Timestamp extends \Module\Orm\Type\Pdo\Date {

	/**
	 * @return string
	 */
	public function format() {
		return 'YmdHis';
	}

}