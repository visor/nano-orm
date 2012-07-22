<?php

namespace Module\Orm\Test\Classes\Model\Mapper;

class Address extends \Module\Orm\Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = 'Module\Orm\Test\Classes\Model\Address';

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
		);
	}

	protected function beforeInsert(\Module\Orm\Model $model) {
		/** @var \Module\Orm\Test\Classes\Model\Address */
		$model->beforeInsert = 1;
	}

	protected function beforeUpdate(\Module\Orm\Model $model) {
		/** @var \Module\Orm\Test\Classes\Model\Address $model */
		$model->beforeUpdate = 1;
	}

	protected function afterInsert(\Module\Orm\Model $model) {
		/** @var \Module\Orm\Test\Classes\Model\Address $model */
		$model->afterInsert = 1;
	}

	protected function afterUpdate(\Module\Orm\Model $model) {
		/** @var \Module\Orm\Test\Classes\Model\Address $model */
		$model->afterUpdate = 1;
	}

	protected function afterSave(\Module\Orm\Model $model) {
		/** @var \Module\Orm\Test\Classes\Model\Address $model */
		$model->afterSave = 1;
	}

}