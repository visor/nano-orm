<?php

namespace Module\Orm\Type\Mongo;

class Binary implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoBinData $value
	 */
	public function castToModel($value) {
		/** @var \MongoBinData $value */
		return $value->bin;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return new \MongoBinData($value);
	}

}