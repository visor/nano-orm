<?php

namespace Module\Orm\DataSource;

class Mongo extends \Module\Orm\DataSource\Common implements \Module\Orm\DataSource {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'   => 'Integer'
		, 'double'    => 'Double'
		, 'string'    => 'String'
		, 'boolean'   => 'Boolean'
		, 'identity'  => 'Mongo\Identity'
		, 'date'      => 'Mongo\Date'
		, 'binary'    => 'Mongo\Binary'
		, 'reference' => 'Mongo\Reference'
		, 'array'     => 'Mongo\Collection'
		, 'object'    => 'Mongo\Object'
	);

	/**
	 * @var \MongoDB
	 */
	private $db = null;

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(\Module\Orm\Resource $resource, \stdClass $data) {
		try {
			foreach ($resource->fieldNames() as $name) {
				if ($resource->isReadOnly($name)) {
					unSet($data->$name);
				}
			}
			if ($this->isEmptyObject($data)) {
				return false;
			}
			$result = $this->collection($resource->name())->insert($data, array('safe' => true));
			if (null === $result['err']) {
				foreach ($resource->identity() as $name) {
					$data->$name = $this->castToModel($resource, $name, $data->$name);
				}
				return true;
			}
			return false;
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 * @param \Module\Orm\Criteria $where
	 */
	public function update(\Module\Orm\Resource $resource, \stdClass $data, \Module\Orm\Criteria $where) {
		try {
			foreach ($resource->identity() as $name) {
				if ($resource->isReadOnly($name)) {
					unSet($data->$name);
				}
			}
			if ($this->isEmptyObject($data)) {
				return false;
			}
			$result = $this->collection($resource->name())->update($this->criteriaToExpression($resource, $where), $data, array('safe' => true));
			return null === $result['err'];
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria|null $where
	 */
	public function delete(\Module\Orm\Resource $resource, \Module\Orm\Criteria $where = null) {
		if (null === $where) {
			$result = $this->collection($resource->name())->drop();
		} else {
			$result = $this->collection($resource->name())->remove($this->criteriaToExpression($resource, $where), array('safe' => true));
		}
		return (1 == $result['ok']);
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function get(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		try {
			$result = $this->collection($resource->name())->findOne($this->criteriaToExpression($resource, $criteria));
			if (null === $result) {
				return false;
			}
			return $result;
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return int
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function count(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null) {
		try {
			if (null === $criteria) {
				return $this->collection($resource->name())->count();
			}

			return $this->collection($resource->name())->count($this->criteriaToExpression($resource, $criteria));
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 * @param \Module\Orm\FindOptions $findOptions
	 */
	public function find(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null, \Module\Orm\FindOptions $findOptions = null) {
		try {
			if (null === $criteria) {
				$result = $this->collection($resource->name())->find();
			} else {
				$result = $this->collection($resource->name())->find($this->criteriaToExpression($resource, $criteria));
			}
			if (null !== $findOptions) {
				if (null !== $findOptions->getLimitCount()) {
					$result->limit($findOptions->getLimitCount());
				}
				if (null !== $findOptions->getLimitOffset()) {
					$result->skip($findOptions->getLimitOffset());
				}
				if (0 !== count($findOptions->getOrdering())) {
					$sortFields = array();
					foreach ($findOptions->getOrdering() as $field => $ascending) {
						$sortFields[$field] = true === $ascending || null === $ascending ? 1 : -1;
					}
					$result->sort($sortFields);
				}
			}
			$result = iterator_to_array($result);
			if (null === $result) {
				return false;
			}
			return array_values($result);
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param mixed $query
	 */
	public function findCustom(\Module\Orm\Resource $resource, $query) {
		try {
			$options = isSet($query['$options']) ? $query['$options'] : null;
			unSet($query['$options']);
			$result  = $this->collection($resource->name())->find($query);

			if (null !== $options) {
				if (isSet($options['hint'])) {
					$result->hint($options['hint']);
				}
				if (isSet($options['limit'])) {
					$result->limit($options['limit']);
				}
				if (isSet($options['skip'])) {
					$result->skip($options['skip']);
				}
				if (isSet($options['sort'])) {
					$result->sort($options['sort']);
				}
			}

			$result = iterator_to_array($result);
			if (null === $result) {
				return false;
			}
			return array_values($result);
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function criteriaToExpression(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		return \Module\Orm\DataSource\Expression\Mongo::create($this, $resource, $criteria);
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
	 * @return \MongoDB
	 */
	public function db() {
		if (null === $this->db) {
			$mongo = new \Mongo(
				$this->config['server']
				, isSet($this->config['options']) ? $this->config['options'] : array()
			);
			$this->db = $mongo->selectDB($this->config['database']);
		}
		return $this->db;
	}

	/**
	 * @return \MongoCollection
	 * @param string $name
	 */
	protected function collection($name) {
		return $this->db()->selectCollection($name);
	}

}