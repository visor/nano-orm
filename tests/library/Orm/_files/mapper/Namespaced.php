<?php

namespace TestNamespace;

class Mapper_Namespaced extends \NanoOrm_Module\Orm_Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = 'TestNamespace\\Namespaced';

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