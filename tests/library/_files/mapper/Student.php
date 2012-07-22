<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class Student extends \Module\Orm\Mapper {

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
					, 'model'  => '\Module\Orm\Test\Classes\Model\OrmExampleWizard'
					, 'fields' => array('wizardId')
				)
			)
		);
	}

}