<?php

namespace NanoOrm_Module;

interface Orm_Type {

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToModel($value);

	/**
	 * @return mixed
	 * @param mixed $value
	 */
	public function castToDataSource($value);

}