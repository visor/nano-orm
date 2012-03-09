<?php

namespace NanoOrm_Module;

class Orm_Type_Pdo_Mysql_DateTime extends Orm_Type_Pdo_Date {

	/**
	 * @return string
	 */
	public function format() {
		return 'Y-m-d H:i:s';
	}

}