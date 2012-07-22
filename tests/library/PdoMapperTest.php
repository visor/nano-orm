<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class PdoMapperTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\DataSource\Pdo
	 */
	protected $source;

	protected function setUp() {
		include_once $this->files->get($this, '/model/Address.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Wizard.php');
		include_once $this->files->get($this, '/mapper/Wizard.php');

		\Module\Orm\Factory::clearSources();
		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new \Module\Orm\DataSource\Pdo\Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();
		\Module\Orm\Factory::addSource('test', $this->source);
		\Module\Orm\Factory::setDefaultSource('test');
	}

	public function testSavingNewModelIntoDataSource() {
		$address = new \Module\Orm\Test\Classes\Model\Address();
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
		$address = \Module\Orm\Test\Classes\Model\Address::mapper()->get($id);
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
		self::assertTrue(\Module\Orm\Test\Classes\Model\Address::mapper()->get($id)->delete());
		self::assertEquals(0, $this->source->pdo()->query('select count(*) from address')->fetchColumn(0));
	}

	public function testFindShoudlReturnFalseWhenException() {
		self::assertFalse(\Module\Orm\Test\Classes\Model\Address::mapper()->find(\Module\Orm\Factory::criteria()->equals('invalid', 'some')));
	}

	public function testDeleteModelShouldReturnFalseForNewModels() {
		$new = new \Module\Orm\Test\Classes\Model\Address();
		self::assertFalse(\Module\Orm\Test\Classes\Model\Address::mapper()->delete($new));
	}

	public function testSavingUnchangedModelShouldReturnTrue() {
		$wizard = new \Module\Orm\Test\Classes\Model\OrmExampleWizard();
		self::assertFalse($wizard->changed());
		self::assertTrue(\Module\Orm\Test\Classes\Model\OrmExampleWizard::mapper()->save($wizard));
	}

	public function testSavingModelWithoutRequiredFieldsShouldReturnFalse() {
		$wizard = new \Module\Orm\Test\Classes\Model\OrmExampleWizard();
		$wizard->firstName = 'Harry';
		self::assertTrue($wizard->changed());
		self::assertFalse(\Module\Orm\Test\Classes\Model\OrmExampleWizard::mapper()->save($wizard));
		self::assertFalse($wizard->save());

		$address1 = new \Module\Orm\Test\Classes\Model\Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$address1->location = 'The Burrow';
		self::assertTrue($address1->changed());
		self::assertFalse(\Module\Orm\Test\Classes\Model\Address::mapper()->save($address1));
		self::assertFalse($address1->save());
	}

	public function testExceptionShouldThrowWhenCollectionIndexMoreThanFound() {
		$this->setExpectedException('InvalidArgumentException', 'Argument should be between 0 and 1');

		$address1 = new \Module\Orm\Test\Classes\Model\Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = \Module\Orm\Test\Classes\Model\Address::mapper()->find();
		/** @var \Module\Orm\Collection $collection */
		$collection->seek(3);
	}

	public function testExceptionShouldThrowWhenCollectionIndexLessThanZero() {
		$this->setExpectedException('InvalidArgumentException', 'Argument should be between 0 and 1');

		$address1 = new \Module\Orm\Test\Classes\Model\Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = \Module\Orm\Test\Classes\Model\Address::mapper()->find();
		/** @var \Module\Orm\Collection $collection */
		$collection->seek(-1);
	}

	public function testCollectionShouldReturnNullWhenResultsSeeksToEnd() {
		$address1 = new \Module\Orm\Test\Classes\Model\Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = \Module\Orm\Test\Classes\Model\Address::mapper()->find();
		/** @var \Module\Orm\Collection $collection */
		foreach ($collection as $item) {
			self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Address', $item);
		}
		self::assertNull($collection->current());
	}

	public function testFindCustomModels() {
		$address1 = new \Module\Orm\Test\Classes\Model\Address();
		$address1->location = 'Number 4, Privet Drive';
		$address2 = new \Module\Orm\Test\Classes\Model\Address();
		$address2->location = 'The Burrow';
		self::assertTrue($address1->save());
		self::assertTrue($address2->save());

		$collection = \Module\Orm\Test\Classes\Model\Address::mapper()->findCustom('select * from address where location like "%t%" order by id desc');
		/** @var \Module\Orm\Collection $collection */
		self::assertInstanceOf('\Module\Orm\Collection', $collection);
		self::assertEquals(2, $collection->count());

		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Address', $collection[0]);
		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Address', $collection[1]);
		self::assertEquals($collection[0]->location, $address2->location);
		self::assertEquals($collection[1]->location, $address1->location);
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source);
		\Module\Orm\Factory::clearSources();
	}

}