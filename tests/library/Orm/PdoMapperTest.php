<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_PdoMapperTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/model/Address.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Wizard.php');
		include_once $this->files->get($this, '/mapper/Wizard.php');

		Orm::clearSources();
		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new Orm_DataSource_Pdo_Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');
	}

	public function testSavingNewModelIntoDataSource() {
		$address = new Library_Orm_Example_Address();
		$address->location = 'Number 4, Privet Drive';

		self::assertTrue($address->isNew());
		self::assertTrue($address->changed());
		self::assertTrue($address->save());
		self::assertNotNull($address->id);
		self::assertInternalType('integer', $address->id);
		self::assertFalse($address->isNew());
		self::assertFalse($address->changed());
	}

	public function testSavingLoadedModelIntoDataSource() {
		$old  = 'Number 4, Privet Drive';
		$new  = 'Number 4, Privet Drive, Little Whinging';

		$this->source->pdo()->exec('insert into address(location) values (' . $this->source->pdo()->quote('Number 4, Privet Drive'). ')');
		$id = $this->source->pdo()->lastInsertId();

		/** @var \stdClass $address */
		$address = Library_Orm_Example_Address::mapper()->get($id);
		self::assertFalse($address->isNew());
		self::assertEquals($old, $address->location);
		self::assertNotNull($address->id);
		self::assertInternalType('integer', $address->id);

		$address->location = $new;
		self::assertTrue($address->changed());
		self::assertTrue($address->save());
		self::assertFalse($address->changed());
	}

	public function testDeletingModel() {
		$this->source->pdo()->exec('insert into address(location) values (' . $this->source->pdo()->quote('Number 4, Privet Drive'). ')');
		$id = $this->source->pdo()->lastInsertId();

		self::assertEquals(1, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
		self::assertTrue(Library_Orm_Example_Address::mapper()->get($id)->delete());
		self::assertEquals(0, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
	}

	public function testFindShoudlReturnFalseWhenException() {
		self::assertFalse(Library_Orm_Example_Address::mapper()->find(Orm::criteria()->equals('invalid', 'some')));
	}

	public function testDeleteModelShouldReturnFalseForNewModels() {
		$new = new Library_Orm_Example_Address();
		self::assertFalse(Library_Orm_Example_Address::mapper()->delete($new));
	}

	public function testSavingUnchangedModelShouldReturnTrue() {
		$wizard = new Library_OrmExampleWizard();
		self::assertFalse($wizard->changed());
		self::assertTrue(Library_OrmExampleWizard::mapper()->save($wizard));
	}

	public function testSavingModelWithoutRequiredFieldsShouldReturnFalse() {
		$wizard = new Library_OrmExampleWizard();
		$wizard->firstName = 'Harry';
		self::assertTrue($wizard->changed());
		self::assertFalse(Library_OrmExampleWizard::mapper()->save($wizard));
		self::assertFalse($wizard->save());

		$address1 = new Library_Orm_Example_Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new Library_Orm_Example_Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$address1->location = 'The Burrow';
		self::assertTrue($address1->changed());
		self::assertFalse(Library_Orm_Example_Address::mapper()->save($address1));
		self::assertFalse($address1->save());
	}

	public function testExceptionShouldThrowWhenCollectionIndexMoreThanFound() {
		$this->setExpectedException('InvalidArgumentException', 'Argument should be between 0 and 1');

		$address1 = new Library_Orm_Example_Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new Library_Orm_Example_Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = Library_Orm_Example_Address::mapper()->find();
		/** @var Orm_Collection $collection */
		$collection->seek(3);
	}

	public function testExceptionShouldThrowWhenCollectionIndexLessThanZero() {
		$this->setExpectedException('InvalidArgumentException', 'Argument should be between 0 and 1');

		$address1 = new Library_Orm_Example_Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new Library_Orm_Example_Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = Library_Orm_Example_Address::mapper()->find();
		/** @var Orm_Collection $collection */
		$collection->seek(-1);
	}

	public function testCollectionShouldReturnNullWhenResultsSeeksToEnd() {
		$address1 = new Library_Orm_Example_Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new Library_Orm_Example_Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = Library_Orm_Example_Address::mapper()->find();
		/** @var Orm_Collection $collection */
		foreach ($collection as $item) {
			self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $item);
		}
		self::assertNull($collection->current());
	}

	public function testFindCustomModels() {
		$address1 = new Library_Orm_Example_Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new Library_Orm_Example_Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = Library_Orm_Example_Address::mapper()->findCustom('select * from address where location like "%t%" order by id desc');
		/** @var Orm_Collection $collection */
		self::assertInstanceOf('NanoOrm_Module\Orm_Collection', $collection);
		self::assertEquals(2, $collection->count());

		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $collection[0]);
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $collection[1]);
		self::assertEquals($collection[0]->location, $address2->location);
		self::assertEquals($collection[1]->location, $address1->location);
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source);
		Orm::clearSources();
	}

}