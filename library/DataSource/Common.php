<?php

namespace Module\Orm\DataSource;

abstract class Common implements \Module\Orm\DataSource {

	/**
	 * @var \Module\Orm\Type[]
	 */
	protected $typeInstances = array();

	/**
	 * @var string[]
	 */
	protected $supportedTypes = array();

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param array $config
	 */
	public function __construct(array $config) {
		$this->config = $config;
	}

	/**
	 * @return boolean
	 * @param string $typeName
	 */
	public function typeSupported($typeName) {
		return isSet($this->supportedTypes[$typeName]);
	}

	/**
	 * @return \Module\Orm\Type
	 * @param string $typeName
	 *
	 * @throws \Module\Orm\Exception\UnsupportedType
	 */
	public function type($typeName) {
		if (!$this->typeSupported($typeName)) {
			throw new \Module\Orm\Exception\UnsupportedType($typeName);
		}
		if (isSet($this->typeInstances[$typeName])) {
			return $this->typeInstances[$typeName];
		}
		return ($this->typeInstances[$typeName] = $this->createTypeInstance($typeName));
	}

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToModel(\Module\Orm\Resource $resource, $field, $value) {
		if (null === $value) {
			return null;
		}
		return $this->type($resource->typeOf($field))->castToModel($value);
	}

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToDataSource(\Module\Orm\Resource $resource, $field, $value) {
		if (null === $value) {
			return $this->nullValue();
		}
		return $this->type($resource->typeOf($field))->castToDataSource($value);
	}

	/**
	 * @return boolean
	 * @param \stdClass $data
	 */
	protected function isEmptyObject(\stdClass $data) {
		$values = get_object_vars($data);
		return empty($values);
	}

	/**
	 * @param string $typeName
	 * @return \Module\Orm\Type
	 */
	protected function createTypeInstance($typeName) {
		$className = '\Module\Orm\Type' . NS . $this->supportedTypes[$typeName];
		return new $className;
	}

	/**
	 * @return \Module\Orm\DataSource\Common
	 * @param string $name
	 * @param string $class
	 */
	protected function addType($name, $class) {
		$this->supportedTypes[$name] = $class;
		return $this;
	}

}