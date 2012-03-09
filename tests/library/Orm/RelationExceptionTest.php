<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_RelationExceptionTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo
	 */
	protected $source;

	/**
	 * @var Library_OrmExampleWizard
	 */
	protected $wizard;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Wizard.php');
		include_once $this->files->get($this, '/model/Wizard.php');

		Orm::clearSources();
		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new Orm_DataSource_Pdo_Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();
		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');

		$this->wizard = new Library_OrmExampleWizard();
	}

	public function testExceptionShouldThrowWhenNoTypeField() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_IncompletedResource', 'Resource definition is not completed: wizard');
		$this->wizard->addressNoType;
	}

	public function testExceptionShouldThrowWhenUnknownTypeSpecified() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnknownRelationType', 'Relation addressUnknownType with type some-relation-type is not supported');
		$this->wizard->addressUnknownType;
	}

	public function testExceptionShouldThrowWhenUnknownRelationGetted() {
		$this->setExpectedException('NanoOrm_Module\Orm_Exception_UnknownField', 'Unknown resource field: wizard.unknown relation');
		Library_OrmExampleWizard::mapper()->getResource()->getRelation('unknown relation');
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source);
		Orm::clearSources();
	}

}