<?php

namespace Module\Orm\Type;

class Boolean implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return (boolean)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return true === (boolean)$value ? 1 : 0;
	}

}