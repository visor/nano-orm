<?php

namespace NanoOrm_Module;

class Orm_Type_Double implements Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return (double)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (double)$value;
	}

}