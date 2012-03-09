<?php

namespace NanoOrm_Module;

class Orm_Type_Mongo_Array implements Orm_Type {

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