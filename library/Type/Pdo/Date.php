<?php

namespace Module\Orm\Type\Pdo;

abstract class Date implements \Module\Orm\Type {

	/**
	 * @return \Nano\Util\Date
	 * @param string $value
	 */
	public function castToModel($value) {
		return \Nano\Util\Date::createFromFormat($this->modelFormat(), $value);
	}

	/**
	 * @return string
	 * @param \Nano\Util\Date $value
	 */
	public function castToDataSource($value) {
		/** @var \Nano\Util\Date $value */
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