<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class OrmExampleWizard extends \Module\Orm\Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = '\Module\Orm\Test\Classes\Model\OrmExampleWizard';

	/**
	 * @return array
	 */
	protected function getMeta() {
		return array(
			'name'          => 'wizard'
			, 'fields'      => array(
				'id'     => array(
					'type' => 'integer'
				)
				, 'firstName' => array(
					'type'   => 'string'
					, 'null' => false
				)
				, 'lastName' => array(
					'type'   => 'string'
					, 'null' => false
				)
				, 'role' => array(
					'type'      => 'string'
					, 'null'    => false
					, 'default' => 'student'
				)
				, 'addressId' => array(
					'type'   => 'integer'
					, 'null' => false
				)
			)
			, 'incremental' => 'id'
			, 'identity'    => array('id')
			, 'relations'   => array(
				'address' => array(
					'type'     => self::RELATION_TYPE_BELONGS_TO
					, 'model'  => '\Module\Orm\Test\Classes\Model\Address'
					, 'fields' => array('addressId')
				)
				, 'addressNoType' => array(
					'model'    => '\Module\Orm\Test\Classes\Model\Address'
					, 'fields' => array('addressId')
				)
				, 'addressUnknownType' => array(
					'type'     => 'some-relation-type'
					, 'model'  => '\Module\Orm\Test\Classes\Model\Address'
					, 'fields' => array('addressId')
				)
			)
		);
	}

}