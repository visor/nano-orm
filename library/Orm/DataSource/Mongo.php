<?php

namespace NanoOrm_Module;

class Orm_DataSource_Mongo extends Orm_DataSource_Abstract implements Orm_DataSource {

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array(
		'integer'   => 'Integer'
		, 'double'    => 'Double'
		, 'string'    => 'String'
		, 'boolean'   => 'Boolean'
		, 'identify'  => 'Mongo_Identify'
		, 'date'      => 'Mongo_Date'
		, 'binary'    => 'Mongo_Binary'
		, 'reference' => 'Mongo_Reference'
		, 'array'     => 'Mongo_Array'
		, 'object'    => 'Mongo_Object'
	);

	/**
	 * @var \MongoDB
	 */
	private $db = null;

	/**
	 * @return mixed
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(Orm_Resource $resource, \stdClass $data) {
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
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 * @param Orm_Criteria $where
	 */
	public function update(Orm_Resource $resource, \stdClass $data, Orm_Criteria $where) {
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
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria|null $where
	 */
	public function delete(Orm_Resource $resource, Orm_Criteria $where = null) {
		if (null === $where) {
			$result = $this->collection($resource->name())->drop();
		} else {
			$result = $this->collection($resource->name())->remove($this->criteriaToExpression($resource, $where), array('safe' => true));
		}
		return (1 == $result['ok']);
	}

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function get(Orm_Resource $resource, Orm_Criteria $criteria) {
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
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 * @param Orm_FindOptions $findOptions
	 */
	public function find(Orm_Resource $resource, Orm_Criteria $criteria = null, Orm_FindOptions $findOptions = null) {
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
	 * @param Orm_Resource $resource
	 * @param mixed $query
	 */
	public function findCustom(Orm_Resource $resource, $query) {
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
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function criteriaToExpression(Orm_Resource $resource, Orm_Criteria $criteria) {
		return Orm_DataSource_Expression_Mongo::create($this, $resource, $criteria);
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