<?php

namespace NanoOrm_Module;

class Orm_Type_Boolean implements Orm_Type {

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