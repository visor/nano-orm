<?php

namespace Module\Orm\Exception;

class UnknownRelationType extends \Module\Orm\Exception {

	public function __construct($name, $type) {
		parent::__construct('Relation ' . $name . ' with type ' . $type . ' is not supported');
	}

}