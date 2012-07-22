<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class Namespaced extends \Module\Orm\Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = 'Module\\Orm\\Test\\Classes\\Model\\Namespaced';

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'          => 'address'
			, 'fields'      => array(
				'id'         => array(
					'type'       => 'integer'
					, 'readonly' => true
				)
				, 'location' => array(
					'type'   => 'string'
					, 'null' => false
				)
			)
			, 'incremental' => 'id'
			, 'identity'    => array('id')
			, 'hasMany'     => array()
			, 'belongsTo'   => array()
		);
	}

}