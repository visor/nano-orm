<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class House extends \Module\Orm\Mapper {

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'          => 'house'
			, 'fields'      => array(
				'id'     => array(
					'type' => 'integer'
				)
				, 'name' => array(
					'type'   => 'string'
					, 'null' => false
				)
			)
			, 'incremental' => 'id'
			, 'identity'    => array('id')
			, 'relations'   => array(
				'wizards' => array(
					'type'     => self::RELATION_TYPE_HAS_MANY
					, 'model'  => '\Module\Orm\Test\Classes\Model\OrmExampleWizard'
					, 'fields' => array('houseId')
				)
			)
		);
	}

}