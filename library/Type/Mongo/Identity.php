<?php

namespace Module\Orm\Type\Mongo;

class Identity implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoId $value
	 */
	public function castToModel($value) {
		/** @var \MongoId $value */
		return $value->__toString();
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return new \MongoId($value);
	}

}