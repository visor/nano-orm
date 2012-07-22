<?php

namespace Module\Orm\Type;

class Integer implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		if (is_scalar($value)) {
			return (int)$value;
		}
		return 0;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (int)($value);
	}

}