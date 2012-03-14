<?php

namespace NanoOrm_Module;

class Orm_RuntimeCache {

	/**
	 * @var \ArrayObject
	 */
	protected $storage;

	public function __construct() {
		$this->storage = new \ArrayObject();
	}

	/**
	 * @return null|Orm_Model
	 * @param array $identy
	 */
	public function get(array $identy) {
		$key = $this->identyToKey($identy);
		if ($this->storage->offsetExists($key)) {
			return $this->storage->offsetGet($key);
		}
		return null;
	}

	/**
	 * @return Orm_Model
	 * @param Orm_Model $model
	 */
	public function store(Orm_Model $model) {
		$this->storage->offsetSet($this->identyToKey($model->identity()), $model);
		return $model;
	}

	/**
	 * @return void
	 * @param Orm_Model|array $key
	 */
	public function remove($key) {
		$identy = $this->identyToKey($key instanceof Orm_Model ? $key->identity() : $key);
		if ($this->storage->offsetExists($identy)) {
			$this->storage->offsetUnset($identy);
		}
	}

	/**
	 * @return string
	 * @param array $identy
	 */
	protected function identyToKey(array $identy) {
		return serialize($identy);
	}

}