<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class AddressMongo extends \Module\Orm\Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = '\Module\Orm\Test\Classes\Model\AddressMongo';

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'        => 'address'
			, 'fields'    => array(
				'_id'         => array(
					'type'       => 'identity'
					, 'readonly' => true
				)
				, 'location' => array(
					'type'   => 'string'
					, 'null' => false
				)
			)
			, 'identity'  => array('_id')
			, 'hasMany'   => array()
			, 'belongsTo' => array()
		);
	}

}