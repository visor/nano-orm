<?php

namespace NanoOrm_Module;

class Orm_Type_Pdo_Mysql_Timestamp extends Orm_Type_Pdo_Date {

	/**
	 * @return string
	 */
	public function format() {
		return 'YmdHis';
	}

}