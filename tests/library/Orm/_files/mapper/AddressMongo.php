<?php

namespace NanoOrm_Module;

class Mapper_Library_Orm_Example_AddressMongo extends Orm_Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = 'NanoOrm_Module\\Library_Orm_Example_AddressMongo';

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'        => 'address'
			, 'fields'    => array(
				'_id'         => array(
					'type'       => 'identify'
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