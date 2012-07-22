<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class FacadeTest extends \Nano\TestUtils\TestCase {

	private $dataSource;

	protected function setUp() {
		include_once $this->files->get($this, '/TestDataSource.php');
		include_once $this->files->get($this, '/model/Address.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Namespaced.php');
		include_once $this->files->get($this, '/mapper/Namespaced.php');

		$this->dataSource = new \Module\Orm\Test\Classes\TestDataSource(array());
		\Module\Orm\Factory::clearSources();
		\Module\Orm\Factory::addSource('test-1', $this->dataSource);
	}

	public function testFactoryMethods() {
		self::assertInstanceOf('\Module\Orm\FindOptions', \Module\Orm\Factory::findOptions());
		self::assertNotSame(\Module\Orm\Factory::findOptions(), \Module\Orm\Factory::findOptions());

		self::assertInstanceOf('\Module\Orm\Criteria', \Module\Orm\Factory::criteria());
		self::assertNotSame(\Module\Orm\Factory::criteria(), \Module\Orm\Factory::criteria());

		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Mapper\Address', \Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Address'));
		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Mapper\Namespaced', \Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Namespaced'));

		self::assertSame(\Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Address'), \Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Address'));
		self::assertSame(\Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Namespaced'), \Module\Orm\Factory::mapper('\Module\Orm\Test\Classes\Model\Namespaced'));
	}

	public function testConfigureShouldThrowWhenUnknownDataSourceClassPassed() {
		$this->setExpectedException('\Module\Orm\Exception\UnknownDataSource', 'Unknown data source implementation \'Class_Not_Exists\'');
		\Module\Orm\Factory::configure(array('some' => array('datasource' => 'Class_Not_Exists')));
	}

	public function testConfigureShouldThrowWhenNonDataSourceClassPassed() {
		$this->setExpectedException('\Module\Orm\Exception\UnknownDataSource', 'Unknown data source implementation \'stdClass\'');
		\Module\Orm\Factory::configure(array('some' => array('datasource' => 'stdClass')));
	}

	public function testConfigureShouldThrowWhenNoDataSourceClassSpecified() {
		$this->setExpectedException('\Module\Orm\Exception\InvalidDataSourceConfiguration', 'Invalid configuration for data source \'some\'');
		\Module\Orm\Factory::configure(array('some' => array()));
	}

	public function testConfigureShouldAddAllSourcesFromDataSource() {
		\Module\Orm\Factory::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Sqlite'
			)
			, 'test-pdo-mysql' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Mysql'
			)
			, 'test-mongo' => array(
				'datasource' => '\Module\Orm\DataSource\Mongo'
			)
		));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Sqlite', \Module\Orm\Factory::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Mysql',  \Module\Orm\Factory::getSource('test-pdo-mysql'));
		self::assertInstanceOf('\Module\Orm\DataSource\Mongo',      \Module\Orm\Factory::getSource('test-mongo'));

		self::assertNull(self::getObjectProperty('Module\Orm\Factory', 'defaultSource'));
	}

	public function testConfigureShouldSetDefaultDataSourceWhenSpecified() {
		\Module\Orm\Factory::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Sqlite'
			)
			, 'test-pdo-mysql' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Mysql'
				, 'default'  => true
			)
			, 'test-mongo' => array(
				'datasource' => '\Module\Orm\DataSource\Mongo'
			)
		));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Sqlite', \Module\Orm\Factory::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Mysql',  \Module\Orm\Factory::getSource('test-pdo-mysql'));
		self::assertInstanceOf('\Module\Orm\DataSource\Mongo',      \Module\Orm\Factory::getSource('test-mongo'));
		self::assertEquals('test-pdo-mysql', self::getObjectProperty('Module\Orm\Factory', 'defaultSource'));
	}

	public function testConfigureShouldSetSourceForModelWhenSpecified() {
		\Module\Orm\Factory::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Sqlite'
				, 'default'  => true
			)
			, 'test-pdo-mysql' => array(
				'datasource' => '\Module\Orm\DataSource\Pdo\Mysql'
				, 'models'   => array('SomeModel')
			)
			, 'test-mongo' => array(
				'datasource' => '\Module\Orm\DataSource\Mongo'
			)
		));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Sqlite', \Module\Orm\Factory::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('\Module\Orm\DataSource\Pdo\Mysql',  \Module\Orm\Factory::getSource('test-pdo-mysql'));
		self::assertInstanceOf('\Module\Orm\DataSource\Mongo',      \Module\Orm\Factory::getSource('test-mongo'));
		self::assertSame(\Module\Orm\Factory::getSource('test-pdo-sqlite'), \Module\Orm\Factory::getSourceFor('DefaultModel'));
		self::assertSame(\Module\Orm\Factory::getSource('test-pdo-mysql'),  \Module\Orm\Factory::getSourceFor('SomeModel'));
	}

	public function testSetDefaultShouldThrowWhenDataSourceNotDefinedButPassed() {
		$this->setExpectedException('\Module\Orm\Exception\InvalidDataSource', 'Invalid DataSource \'some\'');
		\Module\Orm\Factory::setDefaultSource('some');
	}

	public function testSettingUpDataSources() {
		self::assertSame($this->dataSource, \Module\Orm\Factory::getSource('test-1'));

		$source2 = new \Module\Orm\Test\Classes\TestDataSource(array());
		\Module\Orm\Factory::addSource('test-2', $source2);
		self::assertNotSame($this->dataSource, \Module\Orm\Factory::getSource('test-2'));
		self::assertNotSame($source2, \Module\Orm\Factory::getSource('test-1'));
		self::assertSame($source2, \Module\Orm\Factory::getSource('test-2'));
	}

	public function testShouldThrowExceptionWhenDataSourceNotExists() {
		$this->setExpectedException('\Module\Orm\Exception\InvalidDataSource', 'Invalid DataSource \'unknown-data-source\'');
		\Module\Orm\Factory::getSource('unknown-data-source');
	}

	public function testShouldClearSourcesArrayAndDefaultSource() {
		\Module\Orm\Factory::setDefaultSource('test-1');
		\Module\Orm\Factory::setSourceFor('test-1', 'test');
		\Module\Orm\Factory::clearSources();

		self::assertEquals(array(), self::getObjectProperty('Module\Orm\Factory', 'dataSources'));
		self::assertEquals(array(), self::getObjectProperty('Module\Orm\Factory', 'resourcesSource'));
		self::assertNull(self::getObjectProperty('Module\Orm\Factory', 'defaultSource'));
	}

	public function testShouldReturnDefaultSourceForResourceIfNotOneSpecified() {
		\Module\Orm\Factory::setDefaultSource('test-1');
		self::assertSame(\Module\Orm\Factory::getSource('test-1'), \Module\Orm\Factory::getSourceFor('\Module\Orm\Test\Classes\Model\Address'));
	}

	public function testShouldReturnSpecifedResourceWhenSpecified() {
		$source2 = new \Module\Orm\Test\Classes\TestDataSource(array());
		$source3 = new \Module\Orm\Test\Classes\TestDataSource(array());

		\Module\Orm\Factory::setDefaultSource('test-1');
		\Module\Orm\Factory::addSource('test-2', $source2);
		\Module\Orm\Factory::addSource('test-3', $source3);
		\Module\Orm\Factory::setSourceFor('\Module\Orm\Test\Classes\Model\House', 'test-2');
		\Module\Orm\Factory::setSourceFor(array('\Module\Orm\Test\Classes\Model\OrmExampleWizard' => 'test-3'));

		self::assertSame($this->dataSource, \Module\Orm\Factory::getSourceFor('\Module\Orm\Test\Classes\Model\Address'));
		self::assertSame($source2, \Module\Orm\Factory::getSourceFor('\Module\Orm\Test\Classes\Model\House'));
		self::assertSame($source3, \Module\Orm\Factory::getSourceFor('\Module\Orm\Test\Classes\Model\OrmExampleWizard'));
	}

	public function testShouldThrowExceptionWhenDefaultSourceNotSetButRequired() {
		$this->setExpectedException('\Module\Orm\Exception\NoDefaultDataSource', 'Default data source not specified but required');
		\Module\Orm\Factory::getSourceFor('\Module\Orm\Test\Classes\Model\Address');
	}

	protected function tearDown() {
		unSet($this->dataSource);
		\Module\Orm\Factory::clearSources();
	}

}