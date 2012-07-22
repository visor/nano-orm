<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
abstract class TestPdoSource extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\DataSource\Pdo
	 */
	protected $source;

	/**
	 * @var \Module\Orm\Mapper
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Address.php');

		\Module\Orm\Factory::clearSources();
		$this->mapper = new \Module\Orm\Test\Classes\Model\Mapper\Address();
		$this->source = $this->createDataSource();
		$this->source->pdo()->beginTransaction();
		\Module\Orm\Factory::addSource('test', $this->source);
	}

	/**
	 * @abstract
	 * @return \Module\Orm\DataSource\Pdo
	 */
	abstract protected function createDataSource();

	public function testInsertingSimpleResource() {
		$query = 'select count(*) from address';
		$data  = new \stdClass;
		$data->location = 'Number 4, Privet Drive';

		self::assertEquals(0, $this->source->pdo()->query($query)->fetchColumn(0));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $data));
		self::assertEquals(1, $this->source->pdo()->query($query)->fetchColumn(0));

		self::assertObjectHasAttribute('id', $data);
		self::assertEquals($this->source->pdo()->lastInsertId('address'), $data->id);
		self::assertEquals($data, $this->source->pdo()->query('select id, location from address')->fetch(\PDO::FETCH_OBJ));
	}

	public function testInsertingShouldIgnoreReadonlyFields() {
		$countQuery     = 'select count(*) from address';
		$data           = new \stdClass;
		$id             = 'should be ignored';
		$data->id       = $id;
		$data->location = 'Number 4, Privet Drive';

		self::assertEquals(0, $this->source->pdo()->query($countQuery)->fetchColumn(0));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $data));
		self::assertEquals(1, $this->source->pdo()->query($countQuery)->fetchColumn(0));

		self::assertNotEquals($id, $data->id);
		self::assertFalse($this->source->pdo()->query('select * from address where id = ' . $this->source->pdo()->quote($id))->fetch(\PDO::FETCH_OBJ));

		$saved = $this->source->pdo()->query('select * from address where location = ' . $this->source->pdo()->quote($data->location))->fetch(\PDO::FETCH_OBJ);
		self::assertInstanceOf('stdClass', $saved);
		self::assertEquals($data, $saved);
	}

	public function testInsertShouldReturnFalseForEmptyData() {
		self::assertFalse($this->source->insert($this->mapper->getResource(), new \stdClass));
	}

	public function testInsertShouldFailsWhenException() {
		$data           = new \stdClass;
		$data->location = 'Number 4, Privet Drive';

		self::assertTrue($this->source->insert($this->mapper->getResource(), $data), 'insert unique value');
		self::assertFalse($this->source->insert($this->mapper->getResource(), $data), 'insert duplicate value');
	}

	public function testUpdatingSimpleResource() {
		$original = array('location' => 'Number 4, Privet Drive');
		$this->source->pdo()->exec('insert into address(location) values(' . $this->source->pdo()->quote($original['location']). ')');
		$identity = $this->source->pdo()->lastInsertId();
		$criteria = \Module\Orm\Factory::criteria()->equals('id', $identity);
		$updated  = (object)array('location' => 'Number 4, Privet Drive, Little Whinging');

		self::assertTrue($this->source->update($this->mapper->getResource(), $updated, $criteria));

		$stored = $this->source->pdo()->query('select * from `address` where id = ' . $this->source->pdo()->quote($identity))->fetch(\PDO::FETCH_OBJ);
		self::assertInstanceOf('stdClass', $stored);
		self::assertEquals($updated->location, $stored->location);
	}

	public function testUpdateShouldReturnFalseWhenException() {
		$first  = (object)array('location' => 'Number 4, Privet Drive');
		$second = (object)array('location' => 'Game Hut at Hogwarts');

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));
		self::assertFalse($this->source->update($this->mapper->getResource()
			, (object)array('location' => $second->location)
			, \Module\Orm\Factory::criteria()->equals('id', $first->id)
		));
	}

	public function testUpdateShouldReturnFalseForEmptyData() {
		$original = array('location' => 'Number 4, Privet Drive');
		$this->source->pdo()->exec('insert into address(location) values(' . $this->source->pdo()->quote($original['location']). ')');
		$identity = $this->source->pdo()->lastInsertId();
		$criteria = \Module\Orm\Factory::criteria()->equals('id', $identity);

		self::assertFalse($this->source->update($this->mapper->getResource(), new \stdClass, $criteria));
	}

	public function testDeleteShouldRemoveAllRecordsWhenEmptyCriteria() {
		self::assertTrue($this->source->insert($this->mapper->getResource(), (object)array('location' => 'Number 4, Privet Drive')));
		self::assertTrue($this->source->insert($this->mapper->getResource(), (object)array('location' => 'Game Hut at Hogwarts')));

		self::assertEquals(2, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
		self::assertTrue($this->source->delete($this->mapper->getResource()));
		self::assertEquals(0, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
	}

	public function testDeleteShouldDeleteUsingCriteria() {
		$toDelete = (object)array('location' => 'Number 4, Privet Drive');
		$another  = (object)array('location' => 'Game Hut at Hogwarts');
		self::assertTrue($this->source->insert($this->mapper->getResource(), $toDelete));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $another));
		$criteria = \Module\Orm\Factory::criteria()->equals('id', $toDelete->id);

		self::assertEquals(2, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
		self::assertTrue($this->source->delete($this->mapper->getResource(), $criteria));
		self::assertEquals(1, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
		self::assertEquals(0, $this->source->pdo()->query('select count(*) from address where id = ' . $toDelete->id)->fetchColumn(0));
		self::assertEquals(1, $this->source->pdo()->query('select count(*) from address where id = ' . $another->id)->fetchColumn(0));
	}

	public function testGettingOneRow() {
		$first  = (object)array('location' => 'Number 4, Privet Drive');
		$second = (object)array('location' => 'Game Hut at Hogwarts');
		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		self::assertEquals((array)$first,  $this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('location', $first->location)));
		self::assertEquals((array)$second, $this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('location', $second->location)));
		self::assertEquals((array)$first,  $this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('id', $first->id)));
		self::assertEquals((array)$second, $this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('id', $second->id)));
	}

	public function testGetShouldReturnFalseWhenNoRecords() {
		self::assertFalse($this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('id', 1)));
	}

	public function testGetShouldReturnFalseWhenException() {
		self::assertFalse($this->source->get($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('invalid field', 1)));
	}

	public function testFindRows() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');
		$criteria = \Module\Orm\Factory::criteria()->equals('location', $first->location);

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		self::assertCount(1, $this->source->find($this->mapper->getResource(), $criteria));
		self::assertEquals(array((array)$first), $this->source->find($this->mapper->getResource(), $criteria));
	}

	public function testFindRowsShouldReturnAllRecordsForEmptyCriteria() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));

		self::assertCount(2, $this->source->find($this->mapper->getResource()));
		self::assertEquals(array((array)$first, (array)$second), $this->source->find($this->mapper->getResource(), null, \Module\Orm\Factory::findOptions()->orderBy('id')));
	}

	public function testFindShouldReturnEmptyArrayWhenNoRecords() {
		self::assertCount(0, $this->source->find($this->mapper->getResource()));
		self::assertEquals(array(), $this->source->find($this->mapper->getResource()));
	}

	public function testFindShouldReturnFalseWhenException() {
		self::assertFalse($this->source->find($this->mapper->getResource(), \Module\Orm\Factory::criteria()->equals('invalid', 'some')));
	}

	public function testFindCustomRows() {
		$first    = (object)array('location' => 'Number 4, Privet Drive');
		$second   = (object)array('location' => 'Game Hut at Hogwarts');

		self::assertTrue($this->source->insert($this->mapper->getResource(), $first));
		self::assertTrue($this->source->insert($this->mapper->getResource(), $second));
		$found = $this->source->findCustom($this->mapper->getResource(), 'select * from address where location like "%t%" order by id desc');

		self::assertInternalType('array', $found);
		self::assertCount(2, $found);
		self::assertArrayHasKey('0', $found);
		self::assertArrayHasKey('1', $found);

		self::assertEquals($first->id,        $found[1]['id']);
		self::assertEquals($first->location,  $found[1]['location']);
		self::assertEquals($second->location, $found[0]['location']);
		self::assertEquals($second->id,       $found[0]['id']);
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source, $this->mapper);
		\Module\Orm\Factory::clearSources();
	}

}