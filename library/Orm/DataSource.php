<?php

namespace NanoOrm_Module;

interface Orm_DataSource {

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
	 * @return Orm_Type
	 * @param string $typeName
	 */
	public function type($typeName);

	/**
	 * @return mixed
	 * @param Orm_Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToModel(Orm_Resource $resource, $field, $value);

	/**
	 * @return mixed
	 * @param Orm_Resource $resource
	 * @param string $field
	 * @param mixed $value
	 */
	public function castToDataSource(Orm_Resource $resource, $field, $value);

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 */
	public function insert(Orm_Resource $resource, \stdClass $data);

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param \stdClass $data
	 * @param Orm_Criteria $where
	 */
	public function update(Orm_Resource $resource, \stdClass $data, Orm_Criteria $where);

	/**
	 * @return boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria|null $where
	 */
	public function delete(Orm_Resource $resource, Orm_Criteria $where = null);

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function get(Orm_Resource $resource, Orm_Criteria $criteria);

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 * @param Orm_FindOptions $findOptions
	 */
	public function find(Orm_Resource $resource, Orm_Criteria $criteria = null, Orm_FindOptions $findOptions = null);

	/**
	 * @return array|boolean
	 * @param Orm_Resource $resource
	 * @param mixed $query
	 */
	public function findCustom(Orm_Resource $resource, $query);

	/**
	 * @return mixed
	 * @param Orm_Resource $resource
	 * @param Orm_Criteria $criteria
	 */
	public function criteriaToExpression(Orm_Resource $resource, Orm_Criteria $criteria);

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