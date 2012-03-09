<?php

namespace NanoOrm_Module;

class Orm_Type_Mongo_Binary implements Orm_Type {

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