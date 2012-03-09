<?php

namespace NanoOrm_Module;

abstract class Orm_Mapper {

	const RELATION_TYPE_BELONGS_TO = 'belongsTo';
	const RELATION_TYPE_HAS_ONE    = 'hasOne';
	const RELATION_TYPE_HAS_MANY   = 'hasMany';

	/**
	 * @var string
	 */
	protected $modelClass;

	/**
	 * @var Orm_Resource
	 */
	protected $resource;

	/**
	 * @var RuntimeCache
	 */
	protected $runtimeCache;

	/**
	 * @return Orm_Resource
	 */
	public function getResource() {
		if (null === $this->resource) {
			$this->resource = new Orm_Resource($this->getMeta());
		}
		return $this->resource;
	}

	/**
	 * @return boolean
	 * @param Orm_Model $model
	 */
	public function save(Orm_Model $model) {
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
	 * @param Orm_Model $model
	 */
	public function insert(Orm_Model $model) {
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
	 * @param Orm_Model $model
	 */
	public function update(Orm_Model $model) {
		//todo: make protected
		if (false === $model->changed()) {
			return true;
		}
		return $this->dataSource()->update($this->getResource(), $model->getData(), $this->getIdentifyCriteria($model));
	}

	/**
	 * @return boolean
	 * @param Orm_Model $model
	 */
	public function delete(Orm_Model $model) {
		if ($model->isNew()) {
			return false;
		}
		return $this->dataSource()->delete($this->getResource(), $this->getIdentifyCriteria($model));
	}

	/**
	 * @return Orm_Model
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
			$criteria = Orm::criteria();
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
	 * @return array|boolean
	 * @param null|Orm_Criteria $criteria
	 * @param null|Orm_FindOptions $findOptions
	 */
	public function find(Orm_Criteria $criteria = null, Orm_FindOptions $findOptions = null) {
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
	 * @return Orm_Model
	 * @param Orm_Model $model
	 * @param string $relationName
	 *
	 * @throws Orm_Exception_IncompletedResource
	 * @throws Orm_Exception_UnknownRelationType
	 */
	public function findRelated(Orm_Model $model, $relationName) {
		$relation = $this->getResource()->getRelation($relationName);
		if (!isSet($relation['type'])) {
			throw new Orm_Exception_IncompletedResource($this->getResource());
		}
		switch ($relation['type']) {
			case self::RELATION_TYPE_BELONGS_TO:
				return $this->findBelongsTo($model, $relationName);
			case self::RELATION_TYPE_HAS_ONE:
				return $this->findHasOne($relationName);
			case self::RELATION_TYPE_HAS_MANY:
				return $this->findHasMany($relationName);
		}
		throw new Orm_Exception_UnknownRelationType($relationName, $relation['type']);
	}

	/**
	 * @return Orm_Model|array|boolean
	 * @param string $relationName
	 * @param scalar $relationValue
	 */
	public function findUsingRelation($relationName, $relationValue) {
		return $this->findUsingRelations(array($relationName), array($relationValue));
	}

	/**
	 * @return Orm_Model|array|boolean
	 * @param array $relationsNames
	 * @param array $relationsValues
	 */
	public function findUsingRelations(array $relationsNames, array $relationsValues) {
		//
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
	 * @return Orm_Model
	 * @param array $sourceData
	 */
	public function load(array $sourceData) {
		return new $this->modelClass($sourceData, true);
	}

	/**
	 * @return Orm_RuntimeCache
	 */
	public function runtimeCache() {
		if (null === $this->runtimeCache) {
			$this->runtimeCache = new Orm_RuntimeCache();
		}
		return $this->runtimeCache;
	}

	/**
	 * @return array
	 */
	abstract protected function getMeta();

	/**
	 * @return Orm_DataSource
	 */
	protected function dataSource() {
		return Orm::getSourceFor($this->modelClass);
	}

	protected function paramsToArray(array $parameters) {
		if (isSet($parameters[0]) && is_array($parameters[0])) {
			return array_values($parameters[0]);
		}
		return $parameters;
	}

	/**
	 * @return Orm_Criteria
	 * @param Orm_Model $model
	 */
	protected function getIdentifyCriteria(Orm_Model $model) {
		$result = Orm::criteria();
		foreach ($this->getResource()->identity() as $fieldName) {
			$result->equals($fieldName, $model->__get($fieldName));
		}
		return $result;
	}

	/**
	 * @return Orm_Model|null
	 * @param Orm_Model $model
	 * @param string $relationName
	 */
	protected function findBelongsTo(Orm_Model $model, $relationName) {
		$relation = $this->getResource()->getRelation($relationName);
		$belongs  = $relation['model'];
		/** @var Orm_mapper $mapper */
		$mapper   = $belongs::mapper();
		$identity = array();
		foreach ($mapper->getResource()->identity() as $index => $fieldName) {
			$identity[$fieldName] = $model->__get($relation['fields'][$index]);
		}
		return $mapper->get($identity);
	}

	protected function findHasOne($relationName) {
	}

	protected function findHasMany($relationName) {
	}

	/**
	 * @return Orm_Collection|boolean
	 * @param array|boolean $elements
	 */
	protected function collectionFactory($elements) {
		if (false === $elements) {
			return false;
		}
		return new Orm_Collection($this, $elements);
	}

	protected function beforeInsert(Orm_Model $model) {}

	protected function beforeUpdate(Orm_Model $model) {}

	protected function afterInsert(Orm_Model $model) {}

	protected function afterUpdate(Orm_Model $model) {}

	protected function afterSave(Orm_Model $model) {}

}