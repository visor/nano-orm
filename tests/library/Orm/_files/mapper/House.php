<?php

namespace NanoOrm_Module;

class Mapper_LibraryOrmExampleHouse extends Orm_Mapper {

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
					, 'model'  => 'NanoOrm_Module\\Library_OrmExampleWizard'
					, 'fields' => array('houseId')
				)
			)
		);
	}

}