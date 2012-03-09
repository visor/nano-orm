<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_RelationBelongsToTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo
	 */
	protected $source;

	protected
		$address1     = 'Number 4, Privet Drive'
		, $firstName1 = 'Harry'
		, $lastName1  = 'Potter'
		, $address2   = 'The Burrow'
	;

	/**
	 * @var Library_Orm_Example_Address
	 */
	protected $addressOne, $addressTwo;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Wizard.php');
		include_once $this->files->get($this, '/mapper/Address.php');
		include_once $this->files->get($this, '/model/Wizard.php');
		include_once $this->files->get($this, '/model/Address.php');

		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new Orm_DataSource_Pdo_Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();

		Orm::clearSources();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');

		$this->addressOne = new Library_Orm_Example_Address();
		$this->addressOne->location = $this->address1;
		$this->addressOne->save();

		$this->addressTwo = new Library_Orm_Example_Address();
		$this->addressTwo->location = $this->address2;
		$this->addressTwo->save();

		$wizard1 = new Library_OrmExampleWizard();
		$wizard1->firstName = $this->firstName1;
		$wizard1->lastName  = $this->lastName1;
		$wizard1->addressId = $this->addressOne->id;
		$wizard1->save();
	}

	public function testGetRelationObject() {
		$wizard = Library_OrmExampleWizard::mapper()->find(Orm::criteria()
			->equals('firstName', $this->firstName1)
			->equals('lastName', $this->lastName1)
		);
		self::assertInstanceOf('NanoOrm_Module\Orm_Collection', $wizard);
		self::assertEquals(1, count($wizard));
		self::assertInstanceOf('NanoOrm_Module\Library_OrmExampleWizard', $wizard[0]);

		$harryPotter = $wizard[0];
		self::assertEquals($harryPotter->addressId, $this->addressOne->id);
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $harryPotter->address);
		self::assertEquals($harryPotter->address->id, $this->addressOne->id);
		self::assertSame($harryPotter->address, $harryPotter->address);
	}

	public function testRelatedObjectectShouldBeNullWhenWrongIdentyPassed() {
		$wizard = new Library_OrmExampleWizard();
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
		Orm::clearSources();
	}

}