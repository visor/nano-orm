<?php

namespace NanoOrm_Module;

class Orm_Type_Integer implements Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return (int)$value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return (int)($value);
	}

}