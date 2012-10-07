<?php

namespace Module\Orm\DataSource;

abstract class Pdo extends \Module\Orm\DataSource\Common implements \Module\Orm\DataSource {

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

			if (isSet($config['debug']) && true === $config['debug']) {
				$this->pdo = new \Module\Orm\Pdo\Connection($config['dsn'], $userName, $password, $options);
				$this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS, array('Module\Orm\Pdo\Statement'));
			} else {
				$this->pdo = new \PDO($config['dsn'], $userName, $password, $options);
			}

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
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(\Module\Orm\Resource $resource, \stdClass $data) {
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
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 * @param \Module\Orm\Criteria $where
	 */
	public function update(\Module\Orm\Resource $resource, \stdClass $data, \Module\Orm\Criteria $where) {
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
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria|null $where
	 */
	public function delete(\Module\Orm\Resource $resource, \Module\Orm\Criteria $where = null) {
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
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function get(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria) {
		try {
			return $this->pdo()->query($this->findQuery($resource, $criteria, \Module\Orm\Factory::findOptions()->limit(1)))->fetch(\PDO::FETCH_ASSOC);
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
			$query = $this->countQuery($resource, $criteria, null);
			return $this->pdo->query($query)->fetchColumn(0);
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
			return $this->findCustom($resource, $this->findQuery($resource, $criteria, $findOptions));
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
			return $this->pdo()->query($query)->fetchAll(\PDO::FETCH_ASSOC);
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
		return \Module\Orm\DataSource\Expression\Pdo::create($this, $resource, $criteria);
	}

	/**
	 * @return string
	 */
	public function nullValue() {
		return self::NULL_VALUE;
	}

	/**
	 * @return string
	 * @param \Module\Orm\Resource $resource
	 * @param array $dataToSave
	 */
	protected function insertQuery(\Module\Orm\Resource $resource, array $dataToSave) {
		return 'insert into ' . $this->quoteName($resource->name()) . '(' . implode(', ', $dataToSave['fields']) . ') values (' . implode(', ', $dataToSave['values']) . ')';
	}

	/**
	 * @return string
	 * @param \Module\Orm\Resource $resource
	 * @param string[] $data
	 * @param \Module\Orm\Criteria $criteria
	 */
	protected function updateQuery(\Module\Orm\Resource $resource, array $data, \Module\Orm\Criteria $criteria) {
		return 'update ' . $this->quoteName($resource->name()) . ' set ' . implode(', ', $data) . ' where ' . $this->criteriaToExpression($resource, $criteria);
	}

	/**
	 * @return string
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	protected function deleteQuery(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null) {
		$result = 'delete from ' . $this->quoteName($resource->name());
		if (null === $criteria) {
			return $result;
		}
		$result .= ' where ' . $this->criteriaToExpression($resource, $criteria);
		return $result;
	}

	/**
	 * @return string
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 * @param \Module\Orm\FindOptions $findOptions
	 */
	protected function findQuery(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null, \Module\Orm\FindOptions $findOptions = null) {
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

	protected function countQuery(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null) {
		$result = 'select count(*) from ' . $this->quoteName($resource->name());
		if (null !== $criteria) {
			$result .= ' where ' . $this->criteriaToExpression($resource, $criteria);
		}
		return $result;
	}

	/**
	 * @return string[]
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	protected function prepareDataToInsert(\Module\Orm\Resource $resource, \stdClass $data) {
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
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	protected function prepareDataToUpdate(\Module\Orm\Resource $resource, \stdClass $data) {
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
	 * @param \Module\Orm\Resource $resource
	 */
	protected function prepareFieldNames(\Module\Orm\Resource $resource) {
		$result = $resource->fieldNames();
		$source = $this;
		array_walk($result, function(&$field) use ($source) {
			/** @var \Module\Orm\DataSource $source */
			$field = $source->quoteName($field);
		});
		return $result;
	}

	/**
	 * @return string
	 * @param \Module\Orm\FindOptions $findOptions
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
	 * @param \Module\Orm\FindOptions $findOptions
	 */
	protected function getLimitClause(\Module\Orm\FindOptions $findOptions) {
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