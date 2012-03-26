<?php

namespace NanoOrm_Module;

class Library_Orm_TestDataSource extends Orm_DataSource_Abstract implements Orm_DataSource {

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
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(Orm_Resource $resource, \stdClass $data) {
		return true;
	}

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 * @param Orm_Criteria $where
	 */
	public function update(Orm_Resource $resource, \stdClass $data, Orm_Criteria $where) {
		return true;
	}

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria|null $where
	 */
	public function delete(Orm_Resource $resource, Orm_Criteria $where = null) {
		return true;
	}

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function get(Orm_Resource $resource, Orm_Criteria $criteria) {
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
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function count(Orm_Resource $resource, Orm_Criteria $criteria = null) {
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
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 * @param Orm_FindOptions $findOptions
	 */
	public function find(Orm_Resource $resource, Orm_Criteria $criteria = null, Orm_FindOptions $findOptions = null) {
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
	 * @param Orm_Resource $resource
	 * @param mixed $query
	 *
	 * @throws \Nano_Exception
	 */
	public function findCustom(Orm_Resource $resource, $query) {
		throw new \Nano_Exception('unsupported');
	}

	/**
	 * @return mixed
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function criteriaToExpression(Orm_Resource $resource, Orm_Criteria $criteria) {
		$result = array();
		foreach ($criteria->parts() as $index => $part) {
			/** @var Orm_Criteria_Expression $part */
			if ($part instanceof Orm_Criteria_Expression) {
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