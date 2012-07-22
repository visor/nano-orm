<?php

namespace Module\Orm\Test\Classes\Model;

/**
 * @property int $id
 * @property string $location
 */
class Address extends \Module\Orm\Model {

	public $beforeInsert = 0;
	public $beforeUpdate = 0;
	public $afterInsert  = 0;
	public $afterUpdate  = 0;
	public $afterSave    = 0;

}