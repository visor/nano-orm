<?php

namespace NanoOrm_Module;

class Orm {

	const MAPPER_PREFIX = 'Mapper';
	const MODEL_PREFIX  = 'Model';

	/**
	 * @var Orm_Mapper[]
	 */
	private static $mappers = array();

	/**
	 * @var Orm_DataSource[]
	 */
	private static $dataSources = array();

	/**
	 * @var null|string
	 */
	private static $defaultSource = null;

	/**
	 * @var string[]
	 */
	private static $resourcesSource = array();

	/**
	 * @return void
	 * @param array $options
	 */
	public static function configure(array $options) {
		foreach ($options as $key => $dataSourceOptions) {
			self::buildDataSource($key, (array)$dataSourceOptions);
		}
	}

	/**
	 * @return void
	 * @param string $key
	 * @param Orm_DataSource $source
	 */
	public static function addSource($key, Orm_DataSource $source) {
		self::$dataSources[$key] = $source;
	}

	/**
	 * @static
	 * @return string[]
	 */
	public static function getSourceNames() {
		return array_keys(self::$dataSources);
	}

	/**
	 * @return Orm_DataSource
	 * @param string $key
	 *
	 * @throws Orm_Exception_InvalidDataSource
	 */
	public static function getSource($key) {
		if (isSet(self::$dataSources[$key])) {
			return self::$dataSources[$key];
		}
		throw new Orm_Exception_InvalidDataSource($key);
	}

	/**
	 * @return void
	 * @param string $key
	 *
	 * @throws Orm_Exception_InvalidDataSource
	 */
	public static function setDefaultSource($key) {
		if (!isSet(self::$dataSources[$key])) {
			throw new Orm_Exception_InvalidDataSource($key);
		}
		self::$defaultSource = $key;
	}

	/**
	 * @return void
	 * @param array|string $models
	 * @param null|string $source
	 */
	public static function setSourceFor($models, $source = null) {
		if (is_array($models)) {
			foreach ($models as $model => $source) {
				self::$resourcesSource[$model] = $source;
			}
			return;
		}

		self::$resourcesSource[$models] = $source;
	}

	/**
	 * @return Orm_DataSource
	 * @param string $modelClass
	 */
	public static function getSourceFor($modelClass) {
		if (isSet(self::$resourcesSource[$modelClass])) {
			return self::getSource(self::$resourcesSource[$modelClass]);
		}
		if (null === self::$defaultSource) {
			throw new Orm_Exception_NoDefaultDataSource();
		}
		return self::getSource(self::$defaultSource);
	}

	/**
	 * @return void
	 */
	public static function clearSources() {
		foreach (self::$dataSources as $name => $source) {
			unSet(self::$dataSources[$name]);
		}
		self::$defaultSource   = null;
		self::$dataSources     = array();
		self::$resourcesSource = array();
	}

	/**
	 * @return Orm_Mapper
	 * @param string $model
	 */
	public static function mapper($model) {
		$key = trim(strToLower($model), '\\');
		if (isSet(self::$mappers[$key])) {
			return self::$mappers[$key];
		}

		$class = self::mapperClass($model);
		return (self::$mappers[$key] = new $class);
	}

	/**
	 * @return Orm_Criteria
	 */
	public static function criteria() {
		return Orm_Criteria::create();
	}

	/**
	 * @return Orm_FindOptions
	 */
	public static function findOptions() {
		return Orm_FindOptions::create();
	}

	/**
	 * @return string
	 * @param string $model
	 */
	protected static function mapperClass($model) {
		$pos = strRPos($model, '\\');
		if (false === $pos) {
			return self::MAPPER_PREFIX . '_' . $model;
		}

		$namespace = subStr($model, 0, $pos);
		$class     = subStr($model, $pos + 1);
		return $namespace . '\\' . self::MAPPER_PREFIX . '_' . $class;
	}

	/**
	 * @return void
	 * @param string $key
	 * @param array $options
	 *
	 * @throws Orm_Exception_InvalidDataSourceConfiguration
	 * @throws Orm_Exception_UnknownDataSource
	 */
	protected static function buildDataSource($key, array $options) {
		if (!isSet($options['datasource'])) {
			throw new Orm_Exception_InvalidDataSourceConfiguration($key);
		}
		$class   = $options['datasource'];
		$default = isSet($options['default']) ? true : false;
		$models  = isSet($options['models']) ? $options['models'] : null;

		if (false === class_exists($class)) {
			throw new Orm_Exception_UnknownDataSource($class);
		}
		unSet($options['datasource'], $options['default'], $options['models']);

		$source = new $class($options);
		if (!($source instanceof Orm_DataSource)) {
			throw new Orm_Exception_UnknownDataSource($class);
		}

		self::addSource($key, $source);
		if ($default) {
			self::setDefaultSource($key);
		}
		if ($models) {
			foreach ($models as $model) {
				self::setSourceFor($model, $key);
			}
		}
	}

}