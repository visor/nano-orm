<?php

namespace NanoOrm_Module;

abstract class Orm_Type_Pdo_Date implements Orm_Type {

	/**
	 * @return \Date
	 * @param string $value
	 */
	public function castToModel($value) {
		return \Date::createFromFormat($this->modelFormat(), $value);
	}

	/**
	 * @return string
	 * @param Date $value
	 */
	public function castToDataSource($value) {
		/** @var \Date $value */
		return $value->format($this->dataSourceFormat());
	}

	/**
	 * @return string
	 */
	protected function modelFormat() {
		return $this->format();
	}

	/**
	 * @return string
	 */
	protected function dataSourceFormat() {
		return $this->format();
	}

	/**
	 * @return string
	 */
	abstract public function format();

}