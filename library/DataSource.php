<?php

namespace Module\Orm;

interface DataSource {

	/**
	 * @param array $config
	 */
	public function __construct(array $config);

	/**
	 * @param string $typeName
	 * @return boolean
	 */
	public function typeSupported($typeName);

	/**
	 * @return \Module\Orm\Type
	 * @param string $typeName
	 */
	public function type($typeName);

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToModel(\Module\Orm\Resource $resource, $field, $value);

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToDataSource(\Module\Orm\Resource $resource, $field, $value);

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(\Module\Orm\Resource $resource, \stdClass $data);

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \stdClass $data
	 * @param \Module\Orm\Criteria $where
	 */
	public function update(\Module\Orm\Resource $resource, \stdClass $data, \Module\Orm\Criteria $where);

	/**
	 * @return boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria|null $where
	 */
	public function delete(\Module\Orm\Resource $resource, \Module\Orm\Criteria $where = null);

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function get(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria);

	/**
	 * @return int
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function count(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null);

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 * @param \Module\Orm\FindOptions $findOptions
	 */
	public function find(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria = null, \Module\Orm\FindOptions $findOptions = null);

	/**
	 * @return array|boolean
	 * @param \Module\Orm\Resource $resource
	 * @param mixed $query
	 */
	public function findCustom(\Module\Orm\Resource $resource, $query);

	/**
	 * @return mixed
	 * @param \Module\Orm\Resource $resource
	 * @param \Module\Orm\Criteria $criteria
	 */
	public function criteriaToExpression(\Module\Orm\Resource $resource, \Module\Orm\Criteria $criteria);

	/**
	 * @return string
	 * @param string $name
	 */
	public function quoteName($name);

	/**
	 * @return mixed
	 */
	public function nullValue();

}