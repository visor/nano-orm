<?php

namespace Module\Orm\Exception;

class UnsupportedType extends \Module\Orm\Exception {

	public function __construct($type) {
		parent::__construct('Unsupported type: "' . $type .'"');
	}

}