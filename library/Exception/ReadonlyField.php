<?php

namespace Module\Orm\Exception;

class ReadonlyField extends \Module\Orm\Exception {

	public function __construct(\Module\Orm\Resource $resource, $field) {
		parent::__construct('Field ' . $resource->name() . '.' . $field . ' is read only');
	}

}