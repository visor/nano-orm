<?php

namespace NanoOrm_Module;

class Orm_Type_Pdo_Mysql_Enumeration implements Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		return $value;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return $value;
	}

}