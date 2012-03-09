<?php

namespace NanoOrm_Module;

class Mapper_Library_Orm_Example_AllTypes extends Orm_Mapper {

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'        => 'test-type-casting'
			, 'fields'    => array(
				'integer'  => array(
					'type'   => 'integer'
					, 'null' => false
				)
				, 'double'   => array(
					'type'   => 'double'
					, 'null' => false
				)
				, 'text'     => array(
					'type'   => 'string'
					, 'null' => false
				)
			)
			, 'identity'  => array('integer')
			, 'hasMany'   => array()
			, 'belongsTo' => array()
		);
	}

}