<?php

namespace NanoOrm_Module;

class Mapper_Library_Orm_Example_Address extends Orm_Mapper {

	/**
	 * @var string
	 */
	protected $modelClass = 'NanoOrm_Module\\Library_Orm_Example_Address';

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

	protected function beforeInsert(Orm_Model $model) {
		/** @var Library_Orm_Example_Address $model */
		$model->beforeInsert = 1;
	}

	protected function beforeUpdate(Orm_Model $model) {
		/** @var Library_Orm_Example_Address $model */
		$model->beforeUpdate = 1;
	}

	protected function afterInsert(Orm_Model $model) {
		/** @var Library_Orm_Example_Address $model */
		$model->afterInsert = 1;
	}

	protected function afterUpdate(Orm_Model $model) {
		/** @var Library_Orm_Example_Address $model */
		$model->afterUpdate = 1;
	}

	protected function afterSave(Orm_Model $model) {
		/** @var Library_Orm_Example_Address $model */
		$model->afterSave = 1;
	}

}