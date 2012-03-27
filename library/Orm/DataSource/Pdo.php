<?php

namespace NanoOrm_Module;

abstract class Orm_DataSource_Pdo extends Orm_DataSource_Abstract implements Orm_DataSource {

	const NULL_VALUE = 'null';

	/**
	 * @var \PDO
	 */
	protected $pdo;

	public function __construct(array $config) {
		parent::__construct($config);
		if (isSet($config['dsn'])) {
			$userName  = isSet($config['username']) ? $config['username'] : null;
			$password  = isSet($config['password']) ? $config['password'] : null;
			$options   = isSet($config['options']) ? (array)$config['options'] : array();
			$this->pdo = new \PDO($config['dsn'], $userName, $password, $options);
			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		}
	}

	/**
	 * @return \PDO
	 */
	public function pdo() {
		return $this->pdo;
	}

	/**
	 * @return void
	 * @param \PDO $pdo
	 */
	public function usePdo(\PDO $pdo) {
		$this->pdo = $pdo;
	}

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(Orm_Resource $resource, \stdClass $data) {
		try {
			if ($this->isEmptyObject($data)) {
				return false;
			}
			$toSave     = $this->prepareDataToInsert($resource, $data);
			if (empty($toSave)) {
				return false;
			}
			$saveResult = $this->pdo()->exec($this->insertQuery($resource, $toSave));
			if (0 === $saveResult || false === $saveResult) {
				return false;
			}
			if ($resource->isIncremental()) {
				$id        = $resource->incrementalField();
				$data->$id = $this->castToModel($resource, $id, $this->pdo()->lastInsertId());
			}
			return true;
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
			if ($this->isEmptyObject($data)) {
				return false;
			}
			$toSave = $this->prepareDataToUpdate($resource, $data);
			if ($this->isEmptyObject($data)) {
				return false;
			}
			$result = $this->pdo()->exec($this->updateQuery($resource, $toSave, $where));
			if (false === $result) {
				return false;
			}
			return true;
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
		try {
			$result = $this->pdo()->exec($this->deleteQuery($resource, $where));
			if (false === $result) {
				return false;
			}
			return true;
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function get(Orm_Resource $resource, Orm_Criteria $criteria) {
		try {
			return $this->pdo()->query($this->findQuery($resource, $criteria, Orm::findOptions()->limit(1)))->fetch(\PDO::FETCH_ASSOC);
		} catch (\Exception $e) {
//			Nano_Log::message($e);
			return false;
		}
	}

	/**
	 * @return int
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function count(Orm_Resource $resource, Orm_Criteria $criteria = null) {
		try {
			$query = $this->countQuery($resource, $criteria, null);
			return $this->pdo->query($query)->fetchColumn(0);
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
			return $this->findCustom($resource, $this->findQuery($resource, $criteria, $findOptions));
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
			return $this->pdo()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
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
		return Orm_DataSource_Expression_Pdo::create($this, $resource, $criteria);
	}

	/**
	 * @return string
	 */
	public function nullValue() {
		return self::NULL_VALUE;
	}

	/**
	 * @return string
	 * @param Orm_Resource $resource
	 * @param array $dataToSave
	 */
	protected function insertQuery(Orm_Resource $resource, array $dataToSave) {
		return 'insert into ' . $this->quoteName($resource->name()) . '(' . implode(', ', $dataToSave['fields']) . ') values (' . implode(', ', $dataToSave['values']) . ')';
	}

	/**
	 * @return string
	 * @param Orm_Resource $resource
	 * @param string[] $data
	 * @param Orm_Criteria $criteria
	 */
	protected function updateQuery(Orm_Resource $resource, array $data, Orm_Criteria $criteria) {
		return 'update ' . $this->quoteName($resource->name()) . ' set ' . implode(', ', $data) . ' where ' . $this->criteriaToExpression($resource, $criteria);
	}

	/**
	 * @return string
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	protected function deleteQuery(Orm_Resource $resource, Orm_Criteria $criteria = null) {
		$result = 'delete from ' . $this->quoteName($resource->name());
		if (null === $criteria) {
			return $result;
		}
		$result .= ' where ' . $this->criteriaToExpression($resource, $criteria);
		return $result;
	}

	/**
	 * @return string
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 * @param Orm_FindOptions $findOptions
	 */
	protected function findQuery(Orm_Resource $resource, Orm_Criteria $criteria = null, Orm_FindOptions $findOptions = null) {
		$fields = $this->prepareFieldNames($resource);
		$result = 'select ' . implode(', ', $fields) . ' from ' . $this->quoteName($resource->name());
		if (null !== $criteria) {
			$result .= ' where ' . $this->criteriaToExpression($resource, $criteria);
		}
		if (null === $findOptions) {
			return $result;
		}
		return $result . $this->getOrderClause($findOptions) . $this->getLimitClause($findOptions);
	}

	protected function countQuery(Orm_Resource $resource, Orm_Criteria $criteria = null) {
		$result = 'select count(*) from ' . $this->quoteName($resource->name());
		if (null !== $criteria) {
			$result .= ' where ' . $this->criteriaToExpression($resource, $criteria);
		}
		return $result;
	}

	/**
	 * @return string[]
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	protected function prepareDataToInsert(Orm_Resource $resource, \stdClass $data) {
		$result = array(
			'fields'   => array()
			, 'values' => array()
		);
		foreach ($resource->fieldNames() as $field) {
			if ($resource->isReadOnly($field)) {
				continue;
			}
			$value              = isSet($data->$field) ? $data->$field : $resource->defaultValue($field);
			$result['fields'][] = $this->quoteName($field);
			$result['values'][] = null === $value ? $this->nullValue() : $this->pdo()->quote($this->castToDataSource($resource, $field, $value));
		}
		return $result;
	}

	/**
	 * @return string[]
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	protected function prepareDataToUpdate(Orm_Resource $resource, \stdClass $data) {
		$result = array();
		foreach ($resource->fieldNames() as $field) {
			if ($resource->isReadOnly($field)) {
				continue;
			}

			$value    = isSet($data->$field) ? $data->$field : $resource->defaultValue($field);
			$result[] = $this->quoteName($field) . ' = ' . (null === $value ? $this->nullValue() : $this->pdo()->quote($this->castToDataSource($resource, $field, $value)));
		}
		return $result;
	}

	/**
	 * @return string[]
	 * @param Orm_Resource $resource
	 */
	protected function prepareFieldNames(Orm_Resource $resource) {
		$result = $resource->fieldNames();
		$source = $this;
		array_walk($result, function(&$field) use ($source) {
			/** @var Orm_DataSource $source */
			$field = $source->quoteName($field);
		});
		return $result;
	}

	/**
	 * @return string
	 * @param Orm_FindOptions $findOptions
	 */
	protected function getOrderClause($findOptions) {
		$result = '';
		if (0 === count($findOptions->getOrdering())) {
			return $result;
		}
		$result = ' order by ';
		$first  = true;
		foreach ($findOptions->getOrdering() as $field => $ascending) {
			if (false === $first) {
				$result .= ', ';
			}
			if (null === $ascending || true === $ascending) {
				$result .= $field;
			} else {
				$result .= $field . ' desc';
			}
			$first = false;
		}
		return $result;
	}

	/**
	 * @return string
	 * @param Orm_FindOptions $findOptions
	 */
	protected function getLimitClause(Orm_FindOptions $findOptions) {
		$result = '';
		if (null === $findOptions->getLimitCount()) {
			return $result;
		}
		$result = ' limit ';
		if (null !== $findOptions->getLimitOffset()) {
			$result .= $findOptions->getLimitOffset() . ', ';
		}
		$result .= $findOptions->getLimitCount();
		return $result;
	}

}