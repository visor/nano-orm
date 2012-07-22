<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class RelationBelongsToTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\DataSource\Pdo
	 */
	protected $source;

	protected
		$address1     = 'Number 4, Privet Drive'
		, $firstName1 = 'Harry'
		, $lastName1  = 'Potter'
		, $address2   = 'The Burrow'
	;

	/**
	 * @var \Module\Orm\Test\Classes\Model\Address
	 */
	protected $addressOne, $addressTwo;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Wizard.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Wizard.php');
		include_once $this->files->get($this, '/model/Address.php');

		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new \Module\Orm\DataSource\Pdo\Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();

		\Module\Orm\Factory::clearSources();
		\Module\Orm\Factory::addSource('test', $this->source);
		\Module\Orm\Factory::setDefaultSource('test');

		$this->addressOne = new \Module\Orm\Test\Classes\Model\Address();
		$this->addressOne->location = $this->address1;
		$this->addressOne->save();

		$this->addressTwo = new \Module\Orm\Test\Classes\Model\Address();
		$this->addressTwo->location = $this->address2;
		$this->addressTwo->save();

		$wizard1 = new \Module\Orm\Test\Classes\Model\OrmExampleWizard();
		$wizard1->firstName = $this->firstName1;
		$wizard1->lastName  = $this->lastName1;
		$wizard1->addressId = $this->addressOne->id;
		$wizard1->save();
	}

	public function testGetRelationObject() {
		$wizard = \Module\Orm\Test\Classes\Model\OrmExampleWizard::mapper()->find(\Module\Orm\Factory::criteria()
			->equals('firstName', $this->firstName1)
			->equals('lastName', $this->lastName1)
		);
		self::assertInstanceOf('\Module\Orm\Collection', $wizard);
		self::assertEquals(1, count($wizard));
		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\OrmExampleWizard', $wizard[0]);

		$harryPotter = $wizard[0];
		self::assertEquals($harryPotter->addressId, $this->addressOne->id);
		self::assertInstanceOf('\Module\Orm\Test\Classes\Model\Address', $harryPotter->address);
		self::assertEquals($harryPotter->address->id, $this->addressOne->id);
		self::assertSame($harryPotter->address, $harryPotter->address);
	}

	public function testRelatedObjectectShouldBeNullWhenWrongIdentyPassed() {
		$wizard = new \Module\Orm\Test\Classes\Model\OrmExampleWizard();
		$wizard->addressId = 0;
		self::assertNull($wizard->address);
	}

	public function testSetRelationObjectForNewRecord() {
		self::markTestIncomplete('Not implemented yet');
	}

	public function testSetRelationObjectForExistedRecord() {
		self::markTestIncomplete('Not implemented yet');
	}

	public function testSetRelationPropertiesWhenRelationObjectExists() {
		self::markTestIncomplete('Not implemented yet');
	}

	public function testSetRelationPropertiesWhenRelationObjectNotExists() {
		self::markTestIncomplete('Not implemented yet');
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source, $this->addressOne, $this->addressTwo);
		\Module\Orm\Factory::clearSources();
	}

}