<?php

namespace NanoOrm_Module;

class Orm_Exception_ReadonlyField extends Orm_Exception {

	public function __construct(Orm_Resource $resource, $field) {
		parent::__construct('Field ' . $resource->name() . '.' . $field . ' is read only');
	}

}