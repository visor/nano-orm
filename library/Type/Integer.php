<?php

namespace Module\Orm\Type;

class Integer implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return (int)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (int)($value);
	}

}