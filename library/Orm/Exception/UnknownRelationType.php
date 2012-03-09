<?php

namespace NanoOrm_Module;

class Orm_Exception_UnknownRelationType extends Orm_Exception {

	public function __construct($name, $type) {
		parent::__construct('Relation ' . $name . ' with type ' . $type . ' is not supported');
	}

}