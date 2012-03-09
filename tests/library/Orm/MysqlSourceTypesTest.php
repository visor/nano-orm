<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_MysqlSourceTypesTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo_Mysql
	 */
	protected $source;

	/**
	 * @var Orm_Mapper
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/model/Address.php');

		$this->source = new Orm_DataSource_Pdo_Mysql(array());
	}

	/**
	 * @return array
	 */
	public function getSourceTypes() {
		include_once $this->files->get($this, '/model/AddressMongo.php');
		$source = new Orm_DataSource_Pdo_Mysql(array());
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
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnsupportedType', 'Unsupported type: "' . $type .  '-unsupported"');
		$this->source->type($type . '-unsupported');
	}

	public function testCastingMysqlScalarFields() {
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

	public function testCastingMysqlDateField() {
		$sourceValue = '2010-01-01';
		$modelValue  = \Date::create($sourceValue);
		$this->castingFieldType('string', $sourceValue, $modelValue, 'date');
	}

	public function testCastingMysqlDateTimeField() {
		$sourceValue = '2010-01-01 12:01:02';
		$modelValue  = \Date::create($sourceValue);
		$this->castingFieldType('string', $sourceValue, $modelValue, 'datetime');
	}

	public function testCastingMysqlTimestampField() {
		$sourceValue = '20100101120102';
		$modelValue  = \Date::create('2010-01-01 12:01:02');
		$this->castingFieldType('string', $sourceValue, $modelValue, 'timestamp');
	}

	protected function castingFieldType($typeClass, $sourceValue, $modelValue, $typeName) {
		self::assertEquals($modelValue, $this->source->type($typeName)->castToModel($sourceValue), 'Model value should equals');
		self::assertInternalType($typeClass, $this->source->type($typeName)->castToDataSource($modelValue), 'Internal types should equals');
		self::assertEquals($sourceValue, $this->source->type($typeName)->castToDataSource($modelValue), 'Source values should equals');
	}

	protected function tearDown() {
		unSet($this->source, $this->mapper);
	}

}