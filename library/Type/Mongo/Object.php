<?php

namespace Module\Orm\Type\Mongo;

class Object implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoId $value
	 */
	public function castToModel($value) {
		return (object)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (array)$value;
	}

}