<?php

namespace Module\Orm\Type\Mongo;

class Reference implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoId $value
	 */
	public function castToModel($value) {
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
	}

}