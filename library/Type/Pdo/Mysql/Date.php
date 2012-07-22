<?php

namespace Module\Orm\Type\Pdo\Mysql;

class Date extends \Module\Orm\Type\Pdo\Date {

	public function castToModel($value) {
		return parent::castToModel($value)->midnight();
	}

	/**
	 * @return string
	 */
	public function format() {
		return 'Y-m-d';
	}

}