<?php

namespace NanoOrm_Module;

/**
 * @property int $id
 * @property string $location
 */
class Library_Orm_Example_Address extends Orm_Model {

	public $beforeInsert = 0;
	public $beforeUpdate = 0;
	public $afterInsert  = 0;
	public $afterUpdate  = 0;
	public $afterSave    = 0;

}