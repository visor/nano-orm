<?php

namespace NanoOrm_Module;

class Orm_Type_Mongo_Reference implements Orm_Type {

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