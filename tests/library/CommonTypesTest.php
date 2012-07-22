<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class CommonTypesTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\Test\Classes\TestDataSource
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/TestDataSource.php');
		$this->source = new \Module\Orm\Test\Classes\TestDataSource(array());
	}

	/**
	 * @return array
	 */
	public function getSourceTypes() {
		include_once $this->files->get($this, '/TestDataSource.php');
		$result = array();
		foreach (self::getObjectProperty(new \Module\Orm\Test\Classes\TestDataSource(array()), 'supportedTypes') as $typeName => $className) {
			$result[] = array($typeName);
		}
		return $result;
	}

	/**
	 * @dataProvider getSourceTypes()
	 * @param string $type
	 */
	public function testGettingUnsupportedTypesShouldThrowException($type) {
		$this->setExpectedException('\Module\Orm\Exception\UnsupportedType', 'Unsupported type: "' . $type . '-unsupported"');
		\Module\Orm\Types::getType($this->source, $type . '-unsupported');
	}

	/**
	 * @dataProvider getBooleanValues
	 *
	 * @param mixed $value
	 * @param boolean $expected
	 */
	public function testCastingBooleanToModel($value, $expected) {
		self::assertCasting('boolean', 'boolean', $value, $expected);
	}

	/**
	 * @dataProvider getStringValues
	 *
	 * @param mixed $value
	 * @param boolean $expected
	 */
	public function testCastingStringToModel($value, $expected) {
		self::assertCasting('string', 'string', $value, $expected);
	}

	/**
	 * @dataProvider getIntegerValues
	 *
	 * @param mixed $value
	 * @param boolean $expected
	 */
	public function testCastingIntegerToModel($value, $expected) {
		self::assertCasting('int', 'integer', $value, $expected);
	}

	/**
	 * @dataProvider getDoubleValues
	 *
	 * @param mixed $value
	 * @param boolean $expected
	 */
	public function testCastingDoubleToModel($value, $expected) {
		self::assertCasting('float', 'double', $value, $expected);
	}

	public function getBooleanValues() {
		return array(
			array(0,     false)
			, array('',  false)
			, array('1', true)
			, array(0,   false)
			, array(1,   true)
		);
	}

	public function getStringValues() {
		return array(
			array(100500,                                                '100500')
			, array(array(),                                             'Array')
			, array('string',                                            'string')
			, array(\Nano\Util\Date::create('2000-01-01T01:01:01+0000'), '2000-01-01T01:01:01+0000')
			, array(false,                                               '')
			, array(100.5,                                               '100.5')
		);
	}

	public function getIntegerValues() {
		return array(
			array('100500',                                              100500)
			, array(array(),                                             0)
			, array('string',                                            0)
			, array(\Nano\Util\Date::create('2000-01-01T01:01:01+0000'), 1)
			, array('' ,                                                 0)
			, array('100.1',                                             100)
			, array('100.9',                                             100)
			, array(null,                                                0)
		);
	}

	public function getDoubleValues() {
		return array(
			array('100500',                                              100500.0)
			, array(array(),                                             0.0)
			, array('string',                                            0.0)
			, array(\Nano\Util\Date::create('2000-01-01T01:01:01+0000'), 1.0)
			, array('' ,                                                 0.0)
			, array('100.1',                                             100.1)
			, array('100.9',                                             100.9)
			, array(null,                                                0.0)
		);
	}

	protected function assertCasting($internalType, $type, $value, $expected) {
		self::assertEquals($expected, \Module\Orm\Types::getType($this->source, $type)->castToModel($value), var_export($value, true) . ' should be ' . var_export($expected, true));
		self::assertInternalType($internalType, \Module\Orm\Types::getType($this->source, $type)->castToModel($value), var_export($value, true) . ' should be ' . $type);
	}

	protected function tearDown() {
		$this->source = null;
		self::setObjectProperty('\Module\Orm\Types', 'types', array());
	}

}