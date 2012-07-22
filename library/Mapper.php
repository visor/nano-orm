<?php

namespace Module\Orm;

abstract class Mapper {

	const RELATION_TYPE_BELONGS_TO = 'belongsTo';
	const RELATION_TYPE_HAS_ONE    = 'hasOne';
	const RELATION_TYPE_HAS_MANY   = 'hasMany';

	/**
	 * @var string
	 */
	protected $modelClass;

	/**
	 * @var \Module\Orm\Resource
	 */
	protected $resource;

	/**
	 * @var RuntimeCache
	 */
	protected $runtimeCache;

	/**
	 * @return \Module\Orm\Resource
	 */
	public function getResource() {
		if (null === $this->resource) {
			$this->resource = new \Module\Orm\Resource($this->getMeta());
		}
		return $this->resource;
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Model $model
	 */
	public function save(\Module\Orm\Model $model) {
		if (!$model->changed()) {
			return true;
		}
		if ($model->isNew()) {
			$this->beforeInsert($model);
			if ($this->insert($model)) {
				$this->afterInsert($model);
				$this->afterSave($model);
				return true;
			}
			return false;
		}

		$this->beforeUpdate($model);
		if ($this->update($model)) {
			$this->afterUpdate($model);
			$this->afterSave($model);
			return true;
		}
		return false;
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Model $model
	 */
	public function insert(\Module\Orm\Model $model) {
		//todo: make protected
		if (false === $model->changed()) {
			return true;
		}
		if (false === $this->dataSource()->insert($this->getResource(), $model->getData())) {
			return false;
		}
		$this->runtimeCache()->store($model);
		return true;
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Model $model
	 */
	public function update(\Module\Orm\Model $model) {
		//todo: make protected
		if (false === $model->changed()) {
			return true;
		}
		return $this->dataSource()->update($this->getResource(), $model->getData(), $this->getIdentifyCriteria($model));
	}

	/**
	 * @return boolean
	 * @param \Module\Orm\Model $model
	 */
	public function delete(\Module\Orm\Model $model) {
		if ($model->isNew()) {
			return false;
		}
		$this->beforeDelete($model);
		if (true === $this->dataSource()->delete($this->getResource(), $this->getIdentifyCriteria($model))) {
			$this->afterDelete($model);
			return true;
		}
		return false;
	}

	/**
	 * @return \Module\Orm\Model
	 * @param mixed $identity
	 */
	public function get($identity) {
		$values   = $this->paramsToArray(func_get_args());
		$identity = array();
		foreach ($this->getResource()->identity() as $index => $fieldName) {
			$identity[$fieldName] = $values[$index];
		}
		$result   = $this->runtimeCache()->get($identity);
		if (null === $result) {
			$criteria = \Module\Orm\Factory::criteria();
			foreach ($identity as $fieldName => $value) {
				$criteria->equals($fieldName, $value);
			}
			$data = $this->dataSource()->get($this->getResource(), $criteria);
			if (!$data) {
				return null;
			}
			$result = $this->runtimeCache()->store($this->load($data));
		}
		return $result;
	}

	/**
	 * @return int
	 * @param \Module\Orm\Criteria|null $criteria
	 */
	public function count(\Module\Orm\Criteria $criteria = null) {
		return $this->dataSource()->count($this->getResource(), $criteria);
	}

	/**
	 * @return array|boolean
	 * @param null|\Module\Orm\Criteria $criteria
	 * @param null|\Module\Orm\FindOptions $findOptions
	 */
	public function find(\Module\Orm\Criteria $criteria = null, \Module\Orm\FindOptions $findOptions = null) {
		return $this->collectionFactory(
			$this->dataSource()->find($this->getResource(), $criteria, $findOptions)
		);
	}

	public function findCustom($query) {
		return $this->collectionFactory(
			$this->dataSource()->findCustom($this->getResource(), $query)
		);
	}

	/**
	 * @return \Module\Orm\Model
	 * @param \Module\Orm\Model $model
	 * @param string $relationName
	 *
	 * @throws \Module\Orm\Exception\IncompletedResource
	 * @throws \Module\Orm\Exception\UnknownRelationType
	 */
	public function findRelated(\Module\Orm\Model $model, $relationName) {
		$relation = $this->getResource()->getRelation($relationName);
		if (!isSet($relation['type'])) {
			throw new \Module\Orm\Exception\IncompletedResource($this->getResource());
		}
		switch ($relation['type']) {
			case self::RELATION_TYPE_BELONGS_TO:
				return $this->findBelongsTo($model, $relationName);
			case self::RELATION_TYPE_HAS_ONE:
				return $this->findHasOne($relationName);
			case self::RELATION_TYPE_HAS_MANY:
				return $this->findHasMany($relationName);
		}
		throw new \Module\Orm\Exception\UnknownRelationType($relationName, $relation['type']);
	}

	/**
	 * @return \Module\Orm\Model|array|boolean
	 * @param string $relationName
	 * @param mixed $relationValue
	 */
	public function findUsingRelation($relationName, $relationValue) {
		return $this->findUsingRelations(array($relationName), array($relationValue));
	}

	/**
	 * @return \Module\Orm\Model|array|boolean
	 * @param array $relationsNames
	 * @param array $relationsValues
	 */
	public function findUsingRelations(array $relationsNames, array $relationsValues) {
		return false;
	}

	/**
	 * @return void
	 * @param \stdClass $modelData
	 * @param array $sourceData
	 */
	public function mapToModel(\stdClass $modelData, array $sourceData) {
		foreach ($this->getResource()->fields() as $name => $meta) {
			if (isSet($sourceData[$name])) {
				$value = $this->dataSource()->castToModel($this->getResource(), $name, $sourceData[$name]);
			} else {
				$value = $this->getResource()->defaultValue($name);
			}
			$modelData->$name = $value;
		}
	}

	/**
	 * @return array
	 * @param \stdClass $modelData
	 */
	public function mapToDataSource(\stdClass $modelData) {
		$result = array();
		foreach ($this->getResource()->fields() as $name => $meta) {
			$result[$name] = $this->dataSource()->castToDataSource($this->getResource(), $name, $modelData->$name);
		}
		return $result;
	}

	/**
	 * @return \Module\Orm\Model
	 * @param array $sourceData
	 */
	public function load(array $sourceData) {
		return new $this->modelClass($sourceData, true);
	}

	/**
	 * @return \Module\Orm\RuntimeCache
	 */
	public function runtimeCache() {
		if (null === $this->runtimeCache) {
			$this->runtimeCache = new \Module\Orm\RuntimeCache();
		}
		return $this->runtimeCache;
	}

	/**
	 * @return array
	 */
	abstract protected function getMeta();

	/**
	 * @return \Module\Orm\DataSource
	 */
	protected function dataSource() {
		return \Module\Orm\Factory::getSourceFor($this->modelClass);
	}

	protected function paramsToArray(array $parameters) {
		if (isSet($parameters[0]) && is_array($parameters[0])) {
			return array_values($parameters[0]);
		}
		return $parameters;
	}

	/**
	 * @return \Module\Orm\Criteria
	 * @param \Module\Orm\Model $model
	 */
	protected function getIdentifyCriteria(\Module\Orm\Model $model) {
		$result = \Module\Orm\Factory::criteria();
		foreach ($this->getResource()->identity() as $fieldName) {
			$result->equals($fieldName, $model->__get($fieldName));
		}
		return $result;
	}

	/**
	 * @return \Module\Orm\Model|null
	 * @param \Module\Orm\Model $model
	 * @param string $relationName
	 */
	protected function findBelongsTo(\Module\Orm\Model $model, $relationName) {
		$relation = $this->getResource()->getRelation($relationName);
		$belongs  = $relation['model'];
		/** @var \Module\Orm\Mapper $mapper */
		$mapper   = $belongs::mapper();
		$identity = array();
		foreach ($mapper->getResource()->identity() as $index => $fieldName) {
			$identity[$fieldName] = $model->__get($relation['fields'][$index]);
		}
		return $mapper->get($identity);
	}

	protected function findHasOne($relationName) {
		return false;
	}

	protected function findHasMany($relationName) {
		return false;
	}

	/**
	 * @return \Module\Orm\Collection|boolean
	 * @param array|boolean $elements
	 */
	protected function collectionFactory($elements) {
		if (false === $elements) {
			return false;
		}
		return new \Module\Orm\Collection($this, $elements);
	}

	protected function beforeInsert(\Module\Orm\Model $model) {}

	protected function beforeUpdate(\Module\Orm\Model $model) {}

	protected function afterInsert(\Module\Orm\Model $model) {}

	protected function afterUpdate(\Module\Orm\Model $model) {}

	protected function afterSave(\Module\Orm\Model $model) {}

	protected function beforeDelete(\Module\Orm\Model $model) {}

	protected function afterDelete(\Module\Orm\Model $model) {}

}