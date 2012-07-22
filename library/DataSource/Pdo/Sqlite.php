<?php

namespace Module\Orm\DataSource\Pdo;

class Sqlite extends \Module\Orm\DataSource\Pdo {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'       => 'Integer'
		, 'double'      => 'Double'
		, 'string'      => 'String'
		, 'boolean'     => 'Boolean'
	);

	/**
	 * @return string
	 * @param string $name
	 */
	public function quoteName($name) {
		$result = str_replace('.', '].[', $name);
		$result = '[' . $result . ']';
		return $result;
	}

}