<?php

namespace NanoOrm_Module;

class Orm_Exception_UnknownField extends Orm_Exception {

	public function __construct(Orm_Resource $resource, $field) {
		parent::__construct('Unknown resource field: ' . $resource->name() . '.' . $field);
	}

}