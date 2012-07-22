<?php

namespace Module\Orm;

interface Type {

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