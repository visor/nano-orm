<?php

namespace Module\Orm\Type\Pdo\Mysql;

class Set implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		$result = explode(',', $value);
		array_walk($result, 'trim');
		return $result;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return implode(',', $value);
	}

}