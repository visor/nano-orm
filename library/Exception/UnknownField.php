<?php

namespace Module\Orm\Exception;

class UnknownField extends \Module\Orm\Exception {

	public function __construct(\Module\Orm\Resource $resource, $field) {
		parent::__construct('Unknown resource field: ' . $resource->name() . '.' . $field);
	}

}