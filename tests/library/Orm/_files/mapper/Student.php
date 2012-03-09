<?php

namespace NanoOrm_Module;

class Mapper_Library_Orm_Example_Student extends Orm_Mapper {

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'          => 'student'
			, 'fields'      => array(
				'wizardId' => array(
					'type' => 'integer'
					, 'null' => false
				)
				, 'houseId' => array(
					'type'   => 'integer'
					, 'null' => false
				)
				, 'isDAMembmer' => array(
					'type'      => 'boolean'
					, 'null'    => false
					, 'default' => false
				)
			)
			, 'incremental' => false
			, 'identity'    => array('wizardId')
			, 'relations'   => array(
				'wizard' => array(
					'type'     => self::RELATION_TYPE_HAS_ONE
					, 'model'  => 'Library_OrmExampleWizard'
					, 'fields' => array('wizardId')
				)
			)
		);
	}

}