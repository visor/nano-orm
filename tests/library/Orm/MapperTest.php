<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_MapperTest extends \TestUtils_TestCase {

	/***
	 * @var Orm_DataSource
	 */
	protected $source;

	/**
	 * @var stdClass
	 */
	protected $modelData;

	/**
	 * @var Orm_Mapper
	 */
	protected $addressMapper, $wizardMapper;

	protected function setUp() {
		include_once $this->files->get($this, '/TestDataSource.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/mapper/Wizard.php');
		include_once $this->files->get($this, '/model/Wizard.php');
		include_once $this->files->get($this, '/model/Address.php');

		$this->source        = new Library_Orm_TestDataSource(array());
		$this->modelData     = new \stdClass();
		$this->addressMapper = new Mapper_Library_Orm_Example_Address();
		$this->wizardMapper  = new Mapper_Library_OrmExampleWizard();

		Orm::clearSources();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');
	}

	public function testResourceInstanceShouldCreatedOnlyOnce() {
		self::assertSame($this->addressMapper->getResource(), $this->addressMapper->getResource());
	}

	public function testMapToModelShouldCreateFieldsForModeWhenEmptySourceDataPassed() {
		$this->addressMapper->mapToModel($this->modelData, array());
		foreach ($this->addressMapper->getResource()->fieldNames() as $name) {
			self::assertObjectHasAttribute($name, $this->modelData);
			self::assertNull($this->modelData->$name);
		}
	}

	public function testMapToModelShouldSetValuesFromSourceDataToModelData() {
		$sourceData = array('location' => 'Number 4, Privet Drive');
		$this->addressMapper->mapToModel($this->modelData, $sourceData);
		foreach ($this->addressMapper->getResource()->fieldNames() as $name) {
			if ($this->addressMapper->getResource()->isReadOnly($name)) {
				continue;
			}
			self::assertObjectHasAttribute($name, $this->modelData, $name . ' should exists');
			self::assertEquals($sourceData[$name], $this->modelData->$name, $name . ' should be ' . $sourceData[$name]);
		}
	}

	public function testMapToModelShouldCastValuesToTheirTypes() {
		include_once $this->files->get($this, '/mapper/AllTypes.php');
		$mapper     = new Mapper_Library_Orm_Example_AllTypes();
		$sourceData = array(
			'integer'  => '10'
			, 'double'   => '20'
			, 'text'     => 30
		);

		$mapper->mapToModel($this->modelData, $sourceData);
		foreach ($sourceData as $field => $value) {
			self::assertEquals($this->source->type($field)->castToModel($value), $this->modelData->$field);
		}
	}

	public function testMapToModelShouldIgnoreNonResourceFields() {
		$sourceData = array('field1' => 100, 'field2' => 200);
		$this->addressMapper->mapToModel($this->modelData, $sourceData);
		foreach ($this->addressMapper->getResource()->fieldNames() as $name) {
			self::assertObjectHasAttribute($name, $this->modelData);
			self::assertNull($this->modelData->$name);
		}
		foreach ($sourceData as $name => $value) {
			self::assertObjectNotHasAttribute($name, $this->modelData);
		}
	}

	public function testMapToModelShouldSetDefaultValueForNullFieldsIfExists() {
		$sourceData = array(
			'firstName'   => 'Harry'
			, 'lastName'  => 'Potter'
			, 'role'      => null
			, 'addressId' => 1
		);
		$this->wizardMapper->mapToModel($this->modelData, $sourceData);
		self::assertEquals($this->wizardMapper->getResource()->defaultValue('role'), $this->modelData->role);
		$sourceData = array(
			'firstName'   => 'Harry'
			, 'lastName'  => 'Potter'
			, 'addressId' => 1
		);
		$this->wizardMapper->mapToModel($this->modelData, $sourceData);
		self::assertEquals($this->wizardMapper->getResource()->defaultValue('role'), $this->modelData->role);
	}

	public function testMapToDataSourceShouldSetValuesFromModelDataToSourceData() {
		$modelData  = array('location' => 'Number 4, Privet Drive', 'id' => 1);
		$sourceData = $this->addressMapper->mapToDataSource((object)$modelData);
		self::assertInternalType('array', $sourceData);

		foreach ($this->addressMapper->getResource()->fieldNames() as $name) {
			self::assertArrayHasKey($name, $sourceData, $name . ' should exists');
			self::assertEquals($sourceData[$name], $modelData[$name], $name . ' should be ' . $sourceData[$name]);
		}
	}

	public function testMapToDataSourceShouldIgnoreNonResourceFields() {
		$modelData  = array('field1' => 100, 'field2' => 200, 'location' => 'Number 4, Privet Drive', 'id' => 1);
		$sourceData = $this->addressMapper->mapToDataSource((object)$modelData);
		foreach ($this->addressMapper->getResource()->fieldNames() as $name) {
			self::assertArrayHasKey($name, $sourceData);
		}
		self::assertArrayNotHasKey('field1', $sourceData);
		self::assertArrayNotHasKey('field2', $sourceData);
	}

	public function testGettingModelByIdentify() {
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $this->addressMapper->get(1));
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $this->addressMapper->get(array(1)));
		self::assertEquals('Number 4, Privet Drive', $this->addressMapper->get(1)->location);
		self::assertEquals('The Burrow', $this->addressMapper->get(2)->location);
		self::assertEquals('Game Hut at Hogwarts', $this->addressMapper->get(3)->location);
		self::assertEquals('Malfoy Manor', $this->addressMapper->get(4)->location);
	}

	public function testSavingNonChangedModelsShouldReturnTrue() {
		$new = new Library_Orm_Example_Address();
		self::assertTrue($this->addressMapper->insert($new));
		self::assertNull($new->id);
		self::assertNull($new->location);
		self::assertTrue($new->isNew());
		self::assertTrue($this->addressMapper->update($this->addressMapper->get(1)));
	}

	public function testFindModels() {
		$expected = array(
			0   => 'Number 4, Privet Drive'
			, 1 => 'The Burrow'
			, 2 => 'Game Hut at Hogwarts'
			, 3 => 'Malfoy Manor'
		);
		$addresses = $this->addressMapper->find(null, null);
		self::assertInstanceOf('NanoOrm_Module\Orm_Collection', $addresses);
		self::assertCount(4, $addresses);

		foreach ($addresses as $i => $address) {
			self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $address);
			self::assertEquals($expected[$i], $address->location);
		}
	}

	protected function tearDown() {
		unSet($this->source, $this->addressMapper, $this->wizardMapper, $this->modelData);
		Orm::clearSources();
	}

}