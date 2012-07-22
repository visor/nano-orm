<?php

namespace Module\Orm\DataSource\Pdo;

class Mysql extends \Module\Orm\DataSource\Pdo {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'       => 'Integer'
		, 'double'      => 'Double'
		, 'string'      => 'String'
		, 'boolean'     => 'Boolean'
		, 'date'        => 'Pdo\Mysql\Date'
		, 'datetime'    => 'Pdo\Mysql\DateTime'
		, 'timestamp'   => 'Pdo\Mysql\Timestamp'
		, 'enumeration' => 'Pdo\Mysql\Enumeration'
		, 'set'         => 'Pdo\Mysql\Set'
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