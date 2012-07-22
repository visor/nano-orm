<?php

namespace Module\Orm;

class Factory {

	const NAMESPACE_MAPPER = 'Mapper';

	/**
	 * @var \Module\Orm\Mapper[]
	 */
	private static $mappers = array();

	/**
	 * @var \Module\Orm\DataSource[]
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
	 * @param \Module\Orm\DataSource $source
	 */
	public static function addSource($key, \Module\Orm\DataSource $source) {
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
	 * @return \Module\Orm\DataSource
	 * @param string $key
	 *
	 * @throws \Module\Orm\Exception\InvalidDataSource
	 */
	public static function getSource($key) {
		if (isSet(self::$dataSources[$key])) {
			return self::$dataSources[$key];
		}
		throw new \Module\Orm\Exception\InvalidDataSource($key);
	}

	/**
	 * @return void
	 * @param string $key
	 *
	 * @throws \Module\Orm\Exception\InvalidDataSource
	 */
	public static function setDefaultSource($key) {
		if (!isSet(self::$dataSources[$key])) {
			throw new \Module\Orm\Exception\InvalidDataSource($key);
		}
		self::$defaultSource = $key;
	}

	/**
	 * @return null|string
	 */
	public static function getDefaultSource() {
		return self::$defaultSource;
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
	 * @return \Module\Orm\DataSource
	 * @param string $modelClass
	 *
	 * @throws \Module\Orm\Exception\NoDefaultDataSource
	 */
	public static function getSourceFor($modelClass) {
		if (isSet(self::$resourcesSource[$modelClass])) {
			return self::getSource(self::$resourcesSource[$modelClass]);
		}
		if (null === self::$defaultSource) {
			throw new \Module\Orm\Exception\NoDefaultDataSource();
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
	 * @return \Module\Orm\Mapper
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
	 * @return \Module\Orm\Criteria
	 */
	public static function criteria() {
		return \Module\Orm\Criteria::create();
	}

	/**
	 * @return \Module\Orm\FindOptions
	 */
	public static function findOptions() {
		return \Module\Orm\FindOptions::create();
	}

	/**
	 * @return string
	 * @param string $model
	 */
	protected static function mapperClass($model) {
		return str_replace(
			NS . \Nano\Names::NAMESPACE_MODEL . NS,
			NS . \Nano\Names::NAMESPACE_MODEL . NS . self::NAMESPACE_MAPPER . NS,
			$model
		);
	}

	/**
	 * @return void
	 * @param string $key
	 * @param array $options
	 *
	 * @throws \Module\Orm\Exception\InvalidDataSourceConfiguration
	 * @throws \Module\Orm\Exception\UnknownDataSource
	 */
	protected static function buildDataSource($key, array $options) {
		if (!isSet($options['datasource'])) {
			throw new \Module\Orm\Exception\InvalidDataSourceConfiguration($key);
		}
		$class   = $options['datasource'];
		$default = isSet($options['default']) ? true : false;
		$models  = isSet($options['models']) ? $options['models'] : null;

		if (false === class_exists($class)) {
			throw new \Module\Orm\Exception\UnknownDataSource($class);
		}
		unSet($options['datasource'], $options['default'], $options['models']);

		$source = new $class($options);
		if (!($source instanceof \Module\Orm\DataSource)) {
			throw new \Module\Orm\Exception\UnknownDataSource($class);
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