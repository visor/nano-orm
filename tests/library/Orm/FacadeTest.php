<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_FacadeTest extends \TestUtils_TestCase {

	private $dataSource;

	protected function setUp() {
		include_once $this->files->get($this, '/TestDataSource.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/mapper/Namespaced.php');

		$this->dataSource = new Library_Orm_TestDataSource(array());
		Orm::clearSources();
		Orm::addSource('test-1', $this->dataSource);
	}

	public function testFactoryMethods() {
		self::assertInstanceOf('NanoOrm_Module\Orm_FindOptions', Orm::findOptions());
		self::assertNotSame(Orm::findOptions(), Orm::findOptions());

		self::assertInstanceOf('NanoOrm_Module\Orm_Criteria', Orm::criteria());
		self::assertNotSame(Orm::criteria(), Orm::criteria());

		self::assertInstanceOf('NanoOrm_Module\Mapper_Library_Orm_Example_Address', Orm::mapper('NanoOrm_Module\Library_Orm_Example_Address'));
		self::assertInstanceOf('\\TestNamespace\\Mapper_Namespaced', Orm::mapper('TestNamespace\\Namespaced'));
		self::assertSame(Orm::mapper('NanoOrm_Module\Library_Orm_Example_Address'), Orm::mapper('NanoOrm_Module\Library_Orm_Example_Address'));
		self::assertSame(Orm::mapper('TestNamespace\\Namespaced'), Orm::mapper('TestNamespace\\Namespaced'));
		self::assertSame(Orm::mapper('TestNamespace\\Namespaced'), Orm::mapper('\\TestNamespace\\Namespaced'));
	}

	public function testConfigureShouldThrowWhenUnknownDataSourceClassPassed() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnknownDataSource', 'Unknown data source implementation \'Class_Not_Exists\'');
		Orm::configure(array('some' => array('datasource' => 'Class_Not_Exists')));
	}

	public function testConfigureShouldThrowWhenNonDataSourceClassPassed() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnknownDataSource', 'Unknown data source implementation \'stdClass\'');
		Orm::configure(array('some' => array('datasource' => 'stdClass')));
	}

	public function testConfigureShouldThrowWhenNoDataSourceClassSpecified() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_InvalidDataSourceConfiguration', 'Invalid configuration for data source \'some\'');
		Orm::configure(array('some' => array()));
	}

	public function testConfigureShouldAddAllSourcesFromDataSource() {
		Orm::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Sqlite'
			)
			, 'test-pdo-mysql' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Mysql'
			)
			, 'test-mongo' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Mongo'
			)
		));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Sqlite', Orm::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Mysql',  Orm::getSource('test-pdo-mysql'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Mongo',      Orm::getSource('test-mongo'));
		self::assertNull(self::getObjectProperty('NanoOrm_Module\Orm', 'defaultSource'));
	}

	public function testConfigureShouldSetDefaultDataSourceWhenSpecified() {
		Orm::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Sqlite'
			)
			, 'test-pdo-mysql' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Mysql'
				, 'default'  => true
			)
			, 'test-mongo' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Mongo'
			)
		));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Sqlite', Orm::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Mysql',  Orm::getSource('test-pdo-mysql'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Mongo',      Orm::getSource('test-mongo'));
		self::assertEquals('test-pdo-mysql', self::getObjectProperty('NanoOrm_Module\Orm', 'defaultSource'));
	}

	public function testConfigureShouldSetSourceForModelWhenSpecified() {
		Orm::configure(array(
			'test-pdo-sqlite' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Sqlite'
				, 'default'  => true
			)
			, 'test-pdo-mysql' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Pdo_Mysql'
				, 'models'   => array('SomeModel')
			)
			, 'test-mongo' => array(
				'datasource' => 'NanoOrm_Module\Orm_DataSource_Mongo'
			)
		));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Sqlite', Orm::getSource('test-pdo-sqlite'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Pdo_Mysql',  Orm::getSource('test-pdo-mysql'));
		self::assertInstanceOf('NanoOrm_Module\Orm_DataSource_Mongo',      Orm::getSource('test-mongo'));
		self::assertSame(Orm::getSource('test-pdo-sqlite'), Orm::getSourceFor('DefaultModel'));
		self::assertSame(Orm::getSource('test-pdo-mysql'),  Orm::getSourceFor('SomeModel'));
	}

	public function testSetDefaultShouldThrowWhenDataSourceNotDefinedButPassed() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_InvalidDataSource', 'Invalid DataSource \'some\'');
		Orm::setDefaultSource('some');
	}

	public function testSettingUpDataSources() {
		self::assertSame($this->dataSource, Orm::getSource('test-1'));

		$source2 = new Library_Orm_TestDataSource(array());
		Orm::addSource('test-2', $source2);
		self::assertNotSame($this->dataSource, Orm::getSource('test-2'));
		self::assertNotSame($source2, Orm::getSource('test-1'));
		self::assertSame($source2, Orm::getSource('test-2'));
	}

	public function testShouldThrowExceptionWhenDataSourceNotExists() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_InvalidDataSource', 'Invalid DataSource \'unknown-data-source\'');
		Orm::getSource('unknown-data-source');
	}

	public function testShouldClearSourcesArrayAndDefaultSource() {
		Orm::setDefaultSource('test-1');
		Orm::setSourceFor('test-1', 'test');
		Orm::clearSources();

		self::assertEquals(array(), self::getObjectProperty('NanoOrm_Module\Orm', 'dataSources'));
		self::assertEquals(array(), self::getObjectProperty('NanoOrm_Module\Orm', 'resourcesSource'));
		self::assertNull(self::getObjectProperty('NanoOrm_Module\Orm', 'defaultSource'));
	}

	public function testShouldReturnDefaultSourceForResourceIfNotOneSpecified() {
		Orm::setDefaultSource('test-1');
		self::assertSame(Orm::getSource('test-1'), Orm::getSourceFor('NanoOrm_Module\Library_Orm_Example_Address'));
	}

	public function testShouldReturnSpecifedResourceWhenSpecified() {
		$source2 = new Library_Orm_TestDataSource(array());
		$source3 = new Library_Orm_TestDataSource(array());

		Orm::setDefaultSource('test-1');
		Orm::addSource('test-2', $source2);
		Orm::addSource('test-3', $source3);
		Orm::setSourceFor('NanoOrm_Module\LibraryOrmExampleHouse', 'test-2');
		Orm::setSourceFor(array('NanoOrm_Module\Library_OrmExampleWizard' => 'test-3'));

		self::assertSame($this->dataSource, Orm::getSourceFor('NanoOrm_Module\Library_Orm_Example_Address'));
		self::assertSame($source2, Orm::getSourceFor('NanoOrm_Module\LibraryOrmExampleHouse'));
		self::assertSame($source3, Orm::getSourceFor('NanoOrm_Module\Library_OrmExampleWizard'));
	}

	public function testShouldThrowExceptionWhenDefaultSourceNotSetButRequired() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_NoDefaultDataSource', 'Default data source not specified but required');
		Orm::getSourceFor('NanoOrm_Module\Library_Orm_Example_Address');
	}

	protected function tearDown() {
		unSet($this->dataSource);
		Orm::clearSources();
	}

}