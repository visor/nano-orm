<?php

namespace NanoOrm_Module;

class Orm_Type_Mongo_Date implements Orm_Type {

	/**
	 * @return mixed
	 * @param MongoDate $value
	 */
	public function castToModel($value) {
		/** @var \MongoDate $value */
		$result = \Date::createFromFormat('U', $value->sec);
		$result->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		return $result;
	}

	/**
	 * @return mixed
	 * @param Date $value
	 */
	public function castToDataSource($value) {
		return new \MongoDate($value->getTimestamp());
	}

}