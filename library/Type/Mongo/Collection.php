<?php

namespace Module\Orm\Type\Mongo;

class Collection implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoId $value
	 */
	public function castToModel($value) {
		return (array)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (array)$value;
	}

}