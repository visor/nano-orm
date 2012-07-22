<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class MongoMapperTest extends \Nano\TestUtils\TestCase {

	/***
	 * @var \Module\Orm\DataSource\Mongo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/model/AddressMongo.php');
		include_once $this->files->get($this, '/mapper/AddressMongo.php');

		$this->source = new \Module\Orm\DataSource\Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));

		\Module\Orm\Factory::clearSources();
		\Module\Orm\Factory::addSource('test', $this->source);
		\Module\Orm\Factory::setDefaultSource('test');
	}

	public function testSavingNewModelIntoDataSource() {
		$address = new \Module\Orm\Test\Classes\Model\AddressMongo();
		$address->location = 'Number 4, Privet Drive';

		self::assertTrue($address->isNew());
		self::assertTrue($address->changed());
		self::assertTrue($address->save());
		self::assertNotNull($address->_id);
		self::assertInternalType('string', $address->_id);
		self::assertFalse($address->isNew());
		self::assertFalse($address->changed());
	}

	public function testSavingLoadedModelIntoDataSource() {
		$data = array('location' => 'Number 4, Privet Drive');
		$new  = 'Number 4, Privet Drive, Little Whinging';
		$this->source->db()->selectCollection('address')->insert($data);

		$address = \Module\Orm\Test\Classes\Model\AddressMongo::mapper()->get($data['_id']);
		/** @var \stdClass $address */
		self::assertEquals($data['location'], $address->location);
		self::assertFalse($address->isNew());
		self::assertNotNull($address->_id);
		self::assertInternalType('string', $address->_id);

		$address->location = $new;
		self::assertTrue($address->changed());
		self::assertTrue($address->save());
		self::assertFalse($address->isNew());
		self::assertFalse($address->changed());
	}

	public function testDeletingModel() {
		$data = array('location' => 'Number 4, Privet Drive');
		$this->source->db()->selectCollection('address')->insert($data);

		self::assertEquals(1, $this->source->db()->selectCollection('address')->count());
		self::assertTrue(\Module\Orm\Test\Classes\Model\AddressMongo::mapper()->get($data['_id'])->delete());
		self::assertEquals(0, $this->source->db()->selectCollection('address')->count());
	}

	public function testFindShoudlReturnFalseWhenException() {
		self::assertFalse(\Module\Orm\Test\Classes\Model\AddressMongo::mapper()->find(\Module\Orm\Factory::criteria()->equals('invalid', 'some')));
	}

	public function testFindCustomModels() {
		$address1 = new \Module\Orm\Test\Classes\Model\AddressMongo();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\AddressMongo();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = \Module\Orm\Test\Classes\Model\AddressMongo::mapper()->findCustom(array(
			'location'   => array('$regex' => '.*t.*', '$options' => 'i')
			, '$options' => array('sort' => array('location' => 1))
		));
		/** @var \Module\Orm\Collection $collection */
		self::assertInstanceOf('\Module\Orm\Collection', $collection);
		self::assertEquals(2, $collection->count());

		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\AddressMongo', $collection[0]);
		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\AddressMongo', $collection[1]);
		self::assertEquals($collection[0]->location, $address1->location);
		self::assertEquals($collection[1]->location, $address2->location);
	}

	protected function tearDown() {
		$this->source->db()->drop();
		unSet($this->source);
		\Module\Orm\Factory::clearSources();
	}

}