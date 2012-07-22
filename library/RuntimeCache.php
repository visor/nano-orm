<?php

namespace Module\Orm;

class RuntimeCache {

	/**
	 * @var \ArrayObject
	 */
	protected $storage;

	public function __construct() {
		$this->clean();
	}

	/**
	 * @return null|\Module\Orm\Model
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
	 * @return \Module\Orm\Model
	 * @param \Module\Orm\Model $model
	 */
	public function store(\Module\Orm\Model $model) {
		$this->storage->offsetSet($this->identyToKey($model->identity()), $model);
		return $model;
	}

	/**
	 * @return void
	 * @param \Module\Orm\Model|array $key
	 */
	public function remove($key) {
		$identy = $this->identyToKey($key instanceof \Module\Orm\Model ? $key->identity() : $key);
		if ($this->storage->offsetExists($identy)) {
			$this->storage->offsetUnset($identy);
		}
	}

	public function clean() {
		$this->storage = new \ArrayObject();
	}

	/**
	 * @return string
	 * @param array $identy
	 */
	protected function identyToKey(array $identy) {
		return serialize($identy);
	}

}