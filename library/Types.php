<?php

namespace Module\Orm;

class Types {

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
	 * @return \Module\Orm\Type
	 * @param \Module\Orm\DataSource $dataSource
	 * @param string $typeName
	 *
	 * @throws \Module\Orm\Exception\UnsupportedType
	 */
	public static function getType(\Module\Orm\DataSource $dataSource, $typeName) {
		if (!self::isSupported($typeName)) {
			throw new \Module\Orm\Exception\UnsupportedType($typeName);
		}

		if (isSet(self::$types[$typeName])) {
			return self::$types[$typeName];
		}
		return (self::$types[$typeName] = self::typeInstance($dataSource, $typeName));
	}

	/**
	 * @return \Module\Orm\Type
	 * @param \Module\Orm\DataSource $dataSource
	 * @param string $typeName
	 */
	protected static function typeInstance(\Module\Orm\DataSource $dataSource, $typeName) {
		$className = __NAMESPACE__ . '\\Type\\' . self::$supportedTypes[$typeName];
		return new $className;
	}
}