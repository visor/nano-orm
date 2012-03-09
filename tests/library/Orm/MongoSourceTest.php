<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 * @group orm-source
 */
class Library_Orm_MongoSourceTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Mongo
	 */
	protected $source;

	/**
	 * @var Orm_Mapper
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/AddressMongo.php');
		include_once $this->files->get($this, '/model/AddressMongo.php');

		$this->mapper = new Mapper_Library_Orm_Example_AddressMongo();
		$this->source = new Orm_DataSource_Mongo(array(
			'server'     => 'localhost'
			, 'database' => 'nano_test'
		));

		Orm::clearSources();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');
	}

	public function testInsertingSimpleResource() {
		$data = new \stdClass;
		$data->location = 'Number 4, Privet Drive';

		self::assertTrue($this->source->insert($this->mapper->getResource(), $data));
		self::assertEquals(1, $this->source->db()->selectCollection('address')->count());
		$saved = $this->source->db()->selectCollection('address')->findOne(array('location' => $data->location));

		self::assertInternalType('array', $saved);
	}

	public function testInsertingShouldIgnoreIdentityFields() {
		$data           = new \stdClass;
		$identity       = new \MongoId('100000000000000000000001');
		$data->_id      = $identity;
		$data->location = 'Number 4, Privet Drive';

		self::assertTrue($this->source->insert($this->mapper->getResource(), $data));

		self::assertEquals(1, $this->source->db()->selectCollection('address')->count());
		self::assertNull($this->source->db()->selectCollection('address')->findOne(array('_id' => $identity)));

		$saved = $this->source->db()->selectCollection('address')->findOne(array('location' => $data->location));
		self::assertInternalType('array', $saved);
		self::assertNotEquals($identity->__toString(), $saved['_id']->__toString());
		self::assertEquals($data->_id, $saved['_id']->__toString());
	}

	public function testInsertShouldReturnFalseForEmptyData() {
		self::assertFalse($this->source->insert($this->mapper->getResource(), new \stdClass));
	}

	public function testInsertShouldFailsWhenException() {
		$this->source->db()->selectCollection('address')->ensureIndex(array('location' => 1), array('unique' => true));

		$data = new \stdClass;
		$data->location = 'Number 4, Privet Drive';

		self::assertTrue($this->source->insert($this->mapper->getResource(), $data));
		self::assertFalse($this->source->insert($this->mapper->getResource(), $data));
	}

	public function testUpdatingSimpleResource() {
		$original = array('location' => 'Number 4, Privet Drive');
		$updated  = (object)array('location' => 'Number 4, Privet Drive, Little Whinging');
		$this->source->db()->selectCollection('address')->insert($original);

		$identity = $original['_id'];
		$criteria = Orm::criteria()->equals('_id', $identity);

		self::assertTrue($this->source->update($this->mapper->getResource(), $updated, $criteria));
		self::assertEquals(1, $this->source->db()->selectCollection('address')->count());

		$stored = $this->source->db()->selectCollection('address')->findOne(array('_id' => $identity));
		self::assertInternalType('array', $stored);
		self::assertEquals($updated->location, $stored['location']);
	}

	public function testUpdateShouldReturnFalseWhenException() {
		$this->source->db()->selectCollection('address')->ensureIndex(array('location' => 1), array('unique' => true));

		$first     = (object)array('location' => 'Number 4, Privet Drive');
		$second    = (object)array('location' => 'Game Hut at Hogwarts');
		$duplicate = (object)array('location' => $second->location);

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		self::assertFalse($this->source->update($this->mapper->getResource()
			, $duplicate
			, Orm::criteria()->equals('_id', $first->_id)
		));
	}

	public function testUpdateShouldReturnFalseForEmptyData() {
		$original = array('location' => 'Number 4, Privet Drive');
		$this->source->db()->selectCollection('address')->insert($original);

		$criteria = Orm::criteria()->equals('_id', $original['_id']);
		self::assertFalse($this->source->update($this->mapper->getResource(), new \stdClass, $criteria));
	}

	public function testDeleteShouldRemoveAllRecordsWhenEmptyCriteria() {
		self::assertTrue($this->source->insert($this->mapper->getResource(), (object)array('location' => 'Number 4, Privet Drive')));
		self::assertTrue($this->source->insert($this->mapper->getResource(), (object)array('location' => 'Game Hut at Hogwarts')));

		self::assertEquals(2, $this->source->db()->selectCollection('address')->count());
		self::assertTrue($this->source->delete($this->mapper->getResource()));
		self::assertEquals(0, $this->source->db()->selectCollection('address')->count());
	}

	public function testDeleteShouldDeleteUsingCriteria() {
		$toDelete = (object)array('location' => 'Number 4, Privet Drive');
		$another  = (object)array('location' => 'Game Hut at Hogwarts');
		self::assertTrue($this->source->insert($this->mapper->getResource(), $toDelete));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $another));
		$criteria = Orm::criteria()->equals('_id', $toDelete->_id);

		self::assertEquals(2, $this->source->db()->selectCollection('address')->count());
		self::assertTrue($this->source->delete($this->mapper->getResource(), $criteria));
		self::assertEquals(1, $this->source->db()->selectCollection('address')->count());
		self::assertEquals(0, $this->source->db()->selectCollection('address')->count(array('_id' => new \MongoId($toDelete->_id))));
		self::assertEquals(1, $this->source->db()->selectCollection('address')->count(array('_id' => new \MongoId($another->_id))));
	}

	public function testGettingOneRow() {
		$first  = (object)array('location' => 'Number 4, Privet Drive');
		$second = (object)array('location' => 'Game Hut at Hogwarts');

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		self::assertArrayHasKey('_id', $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('location', $first->location)));
		self::assertArrayHasKey('_id', $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('_id', $first->_id)));
		self::assertArrayHasKey('_id', $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('location', $second->location)));
		self::assertArrayHasKey('_id', $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('_id', $second->_id)));

		$foundByLocation = $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('location', $first->location));
		$foundById       = $this->source->get($this->mapper->getResource(), Orm::criteria()->equals('location', $first->location));
		self::assertEquals($foundById, $foundByLocation);
		self::assertEquals($first->_id, $foundById['_id']->__toString());
	}

	public function testGetShouldReturnFalseWhenNoRecords() {
		self::assertFalse($this->source->get($this->mapper->getResource(), Orm::criteria()->equals('_id', 1)));
	}

	public function testGetShouldReturnFalseWhenException() {
		self::assertFalse($this->source->get($this->mapper->getResource(), Orm::criteria()->equals('invalid field', 1)));
	}

	public function testFindRows() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');
		$criteria = Orm::criteria()->equals('location', $first->location);

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		$found = $this->source->find($this->mapper->getResource(), $criteria);
		self::assertInternalType('array', $found);
		self::assertCount(1, $found);
		self::assertArrayHasKey('0', $found);

		$firstFound = $found[0];
		self::assertInternalType('array', $firstFound);
		self::assertArrayHasKey('_id', $firstFound);
		self::assertInstanceOf('MongoId', $firstFound['_id']);
		self::assertArrayHasKey('location', $firstFound);
		self::assertEquals($first->_id, $firstFound['_id']->__toString());
		self::assertEquals($first->location, $firstFound['location']);
	}

	public function testFindRowsShouldReturnAllRecordsForEmptyCriteria() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		$found = $this->source->find($this->mapper->getResource());
		self::assertCount(2, $found);
		self::assertArrayHasKey('0', $found);
		self::assertArrayHasKey('1', $found);

		self::assertEquals($first->_id,      $found[0]['_id']->__toString());
		self::assertEquals($first->location, $found[0]['location']);
		self::assertEquals($second->_id,      $found[1]['_id']->__toString());
		self::assertEquals($second->location, $found[1]['location']);
	}

	public function testFindShouldReturnEmptyArrayWhenNoRecords() {
		self::assertCount(0, $this->source->find($this->mapper->getResource()));
		self::assertEquals(array(), $this->source->find($this->mapper->getResource()));
	}

	public function testFindShouldReturnFalseWhenException() {
		self::assertFalse($this->source->find($this->mapper->getResource(), Orm::criteria()->equals('invalid', 'some')));
	}

	public function testFindCustomRows() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');
		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		$found = $this->source->findCustom($this->mapper->getResource(), array(
			'location'   => array('$regex' => '.*t.*')
			, '$options' => array('sort' => array('location' => -1))
		));

		self::assertInternalType('array', $found);
		self::assertCount(2, $found);
		self::assertArrayHasKey('0', $found);
		self::assertArrayHasKey('1', $found);

		$expectedData = array($first, $second);
		foreach ($found as $actual) {
			$expected = array_shift($expectedData);
			self::assertEquals($expected->_id,       $actual['_id']->__toString());
			self::assertEquals($expected->location,  $actual['location']);
		}
	}

	protected function tearDown() {
		$this->source->db()->drop();
		unSet($this->source, $this->mapper);
	}

}