<?php

namespace NanoOrm_Module;

class Orm_Exception_IncompletedResource extends Orm_Exception {

	public function __construct(Orm_Resource $resource) {
		parent::__construct('Resource definition is not completed: ' . $resource->name());
	}

}