<?php

namespace Module\Orm\Type\Mongo;

class Date implements \Module\Orm\Type {

	/**
	 * @return mixed
	 * @param MongoDate $value
	 */
	public function castToModel($value) {
		/** @var \MongoDate $value */
		$result = \Nano\Util\Date::createFromFormat('U', $value->sec);
		$result->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		return $result;
	}

	/**
	 * @return mixed
	 * @param \Nano\Util\Date $value
	 */
	public function castToDataSource($value) {
		return new \MongoDate($value->getTimestamp());
	}

}