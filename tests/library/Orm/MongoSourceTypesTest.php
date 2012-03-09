<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_MongoSourceTypesTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Mongo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/model/AddressMongo.php');

		$this->source = new Orm_DataSource_Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));
	}

	/**
	 * @return array
	 */
	public function getSourceTypes() {
		include_once $this->files->get($this, '/model/AddressMongo.php');
		$source =  new Orm_DataSource_Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));
		$result = array();
		foreach (self::getObjectProperty($source, 'supportedTypes') as $typeName => $className) {
			$result[] = array($typeName);
		}
		return $result;
	}

	/**
	 * @dataProvider getSourceTypes()
	 * @param stirng $type
	 */
	public function testSupportedTypes($type) {
		self::assertTrue($this->source->typeSupported($type), $type . ' should be supported');
		self::assertFalse($this->source->typeSupported($type . '_fails'), $type . '_fails should be not supported');
	}

	/**
	 * @dataProvider getSourceTypes()
	 * @param stirng $type
	 */
	public function testTypeInstanceShouldCreatedOnlyOnce($type) {
		$instance = $this->source->type($type);
		self::assertSame($instance, $this->source->type($type));
		self::assertSame($this->source->type($type), $this->source->type($type));
	}

	/**
	 * @dataProvider getSourceTypes()
	 * @param stirng $type
	 */
	public function testShouldThrowExceptionWhenRetrievingUnsupportedTypes($type) {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnsupportedType', 'Unsupported type: "' . $type . '-unsupported"');
		$this->source->type($type . '-unsupported');
	}

	public function testCastingMongoIdFields() {
		$modelValue  = '100000000000000000000001';
		$sourceValue = new \MongoId($modelValue);
		$this->castingMondoFieldType('MongoId', $sourceValue, $modelValue, 'identify');
	}

	public function testCastingMongoDateFields() {
		$modelValue  = \Date::create('2010-10-10 12:00:00');
		$sourceValue = new \MongoDate($modelValue->getTimestamp());
		$this->castingMondoFieldType('MongoDate', $sourceValue, $modelValue, 'date');
	}

	public function testCastingMongoBinaryFields() {
		$modelValue  = 'some value';
		$sourceValue = new \MongoBinData($modelValue);
		$this->castingMondoFieldType('MongoBinData', $sourceValue, $modelValue, 'binary');
	}

	public function testCastingMongoArrayFields() {
		$value = array(1, 2, 3);
		self::assertEquals($value, $this->source->type('array')->castToModel($value));
		self::assertEquals($value, $this->source->type('array')->castToDataSource($value));
	}

	public function testCastingMongoObjectFields() {
		$sourceValue = array('field' => 'value');
		$modelValue = (object)$sourceValue;

		self::assertEquals($modelValue, $this->source->type('object')->castToModel($sourceValue));
		self::assertEquals($sourceValue, $this->source->type('object')->castToDataSource($modelValue));
	}

	public function testCastingMongoReferenceFields() {
		self::markTestIncomplete('Not implemented yet');
	}

	public function testCastingMongoScalarFields() {
		$values = array(
			'string'    => array('string value', 'string value')
			, 'double'  => array(10.01, 10.01)
			, 'integer' => array(10, 10)
			, 'boolean' => array(true, 1)
		);
		foreach ($values as $type => $value) {
			list($modelValue, $sourceValue) = $value;
			self::assertEquals($modelValue, $this->source->type($type)->castToModel($sourceValue));
			self::assertEquals($sourceValue, $this->source->type($type)->castToDataSource($modelValue));
		}
	}

	protected function castingMondoFieldType($mongoTypeClass, $sourceValue, $modelValue, $typeName) {
		self::assertEquals($modelValue, $this->source->type($typeName)->castToModel($sourceValue));
		self::assertInstanceOf($mongoTypeClass, $this->source->type($typeName)->castToDataSource($modelValue));
		self::assertEquals($sourceValue, $this->source->type($typeName)->castToDataSource($modelValue));
	}

	protected function tearDown() {
		$this->source->db()->drop();
		unSet($this->source);
	}

}