<?php

namespace Module\Orm\Test\Classes;

class TestDataSource extends \Module\Orm\DataSource\Common implements \Module\Orm\DataSource {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'    => 'Integer'
		, 'double'   => 'Double'
		, 'text'     => 'String'
		, 'string'   => 'String'
		, 'datetime' => 'Date'
		, 'boolean'  => 'Boolean'
	);

	private $database;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->database = include(__DIR__ . DS . 'database.php');
		parent::__construct($config);
	}

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(\Module\Orm\Resource $resource, \stdClass $data) {
		return true;
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 * @param \Module\Orm\Criteria $where
	 */
	public function update(\Module\Orm\Resource $resource, \stdClass $data, \Module\Orm\Criteria $where) {
		return true;
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria|null $where
	 */
	public function delete(\Module\Orm\Resource $resource, \Module\Orm\Criteria $where = null) {
		return true;
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function get(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		$expr = $this->criteriaToExpression($resource, $criteria);
		foreach ($this->database[$resource->name()] as $record) {
			if (true === $this->testExpression($expr, $record)) {
				return $record;
			}
		}
		return false;
	}

	/**
	 * @return int
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function count(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null) {
		if (null === $criteria) {
			return $this->database[$resource->name()];
		}
		$expr   = $this->criteriaToExpression($resource, $criteria);
		$result = 0;
		foreach ($this->database[$resource->name()] as $record) {
			if (true === $this->testExpression($expr, $record)) {
				++$result;
			}
		}
		return $result;
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 * @param \Module\Orm\FindOptions $findOptions
	 */
	public function find(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null, \Module\Orm\FindOptions $findOptions = null) {
		if (null === $criteria) {
			return $this->database[$resource->name()];
		}
		$expr   = $this->criteriaToExpression($resource, $criteria);
		$result = array();
		foreach ($this->database[$resource->name()] as $record) {
			if (true === $this->testExpression($expr, $record)) {
				$result[] = $record;
			}
		}
		return $result;
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param mixed $query
	 *
	 * @throws \Nano\Exception
	 */
	public function findCustom(\Module\Orm\Resource $resource, $query) {
		throw new \Nano\Exception('unsupported');
	}

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function criteriaToExpression(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		$result = array();
		foreach ($criteria->parts() as $index => $part) {
			/** @var \Module\Orm\Criteria\Expression $part */
			if ($part instanceof \Module\Orm\Criteria\Expression) {
				$result[$part->field()] = $part->value();
			}
		}
		return $result;
	}

	/**
	 * @return string
	 * @param string $name
	 */
	public function quoteName($name) {
		return $name;
	}

	/**
	 * @return mixed
	 */
	public function nullValue() {
		return null;
	}

	/**
	 * @return boolean
	 * @param array $expr
	 * @param array $data
	 */
	protected function testExpression(array $expr, array $data) {
		foreach ($expr as $field => $value) {
			if (!isSet($data[$field])) {
				return false;
			}
			if ($data[$field] != $value) {
				return false;
			}
		}
		return true;
	}

}