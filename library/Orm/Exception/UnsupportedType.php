<?php

namespace NanoOrm_Module;

class Orm_Exception_UnsupportedType extends Orm_Exception {

	public function __construct($type) {
		parent::__construct('Unsupported type: "' . $type .'"');
	}

}