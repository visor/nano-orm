<?php

namespace NanoOrm_Module;

class Orm_Type_String implements Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return (string)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (string)$value;
	}

}