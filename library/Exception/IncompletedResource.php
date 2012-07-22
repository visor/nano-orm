<?php

namespace Module\Orm\Exception;

class IncompletedResource extends \Module\Orm\Exception {

	public function __construct(\Module\Orm\Resource $resource) {
		parent::__construct('Resource definition is not completed: ' . $resource->name());
	}

}