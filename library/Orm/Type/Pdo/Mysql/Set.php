<?php

namespace NanoOrm_Module;

class Orm_Type_Pdo_Mysql_Set implements Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value) {
		$result = explode(',', $value);
		array_walk($result, 'trim');
		return $result;
	}

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value) {
		return implode(',', $value);
	}

}