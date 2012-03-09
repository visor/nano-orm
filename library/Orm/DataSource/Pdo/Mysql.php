<?php

namespace NanoOrm_Module;

class Orm_DataSource_Pdo_Mysql extends Orm_DataSource_Pdo {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'       => 'Integer'
		, 'double'      => 'Double'
		, 'string'      => 'String'
		, 'boolean'     => 'Boolean'
		, 'date'        => 'Pdo_Mysql_Date'
		, 'datetime'    => 'Pdo_Mysql_DateTime'
		, 'timestamp'   => 'Pdo_Mysql_Timestamp'
		, 'enumeration' => 'Pdo_Mysql_Enumeration'
		, 'set'         => 'Pdo_Mysql_Set'
	);

	public function __construct(array $config) {
		if (!isSet($config['options'])) {
			$config['options'] = array();
		}
		$config['options'][\PDO::MYSQL_ATTR_INIT_COMMAND] = 'SET NAMES utf8';

		parent::__construct($config);
	}

	/**
	 * @return string
	 * @param string $value
	 */
	public function quoteName($value) {
		$result = str_replace('.', '`.`', $value);
		$result = '`' . $result . '`';
		return $result;
	}

}