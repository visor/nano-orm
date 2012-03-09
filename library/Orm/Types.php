<?php

namespace NanoOrm_Module;

class Orm_Types {

	private static $supportedTypes = array(
		  'identify' => 'Identify'
		, 'int'      => 'Integer'
		, 'integer'  => 'Integer'
		, 'float'    => 'Double'
		, 'double'   => 'Double'
		, 'string'   => 'String'
		, 'text'     => 'String'
		, 'date'     => 'Date'
//		, 'time'     => 'Time'
		, 'datetime' => 'Date'
//		, 'datetime' => 'DateTime'
		, 'boolean'  => 'Boolean'
		, 'enum'     => 'Enumeration'
//		, 'set'      => 'Set'
	);

	/**
	 * @var OrmType[]
	 */
	private static $types = array();

	public static function isSupported($name) {
		return isSet(self::$supportedTypes[$name]);
	}

	/**
	 * @return Orm_Type
	 * @param Orm_DataSource $dataSource
	 * @param string $typeName
	 *
	 * @throws Orm_Exception_UnsupportedType
	 */
	public static function getType(Orm_DataSource $dataSource, $typeName) {
		if (!self::isSupported($typeName)) {
			throw new Orm_Exception_UnsupportedType($typeName);
		}

		if (isSet(self::$types[$typeName])) {
			return self::$types[$typeName];
		}
		return (self::$types[$typeName] = self::typeInstance($dataSource, $typeName));
	}

	/**
	 * @return Orm_Type
	 * @param Orm_DataSource $dataSource
	 * @param string $typeName
	 */
	protected static function typeInstance(Orm_DataSource $dataSource, $typeName) {
		$className = __NAMESPACE__ . '\Orm_Type_' . self::$supportedTypes[$typeName];
		return new $className;
	}
}