<?php

namespace NanoOrm_Module;

class Orm_Type_Pdo_Mysql_Date extends Orm_Type_Pdo_Date {

	public function castToModel($value) {
		return parent::castToModel($value)->midnight();
	}

	/**
	 * @return string
	 */
	public function format() {
		return 'Y-m-d';
	}

}