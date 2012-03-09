<?php

namespace NanoOrm_Module;

class Orm_DataSource_Pdo_Sqlite extends Orm_DataSource_Pdo {

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