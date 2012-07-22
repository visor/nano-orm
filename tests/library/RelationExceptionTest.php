<?php

namespace Module\Orm\Test\Cases;

/**
 * @group library
 * @group orm
 */
class RelationExceptionTest extends \Nano\TestUtils\TestCase {

	/**
	 * @var \Module\Orm\DataSource\Pdo
	 */
	protected $source;

	/**
	 * @var Library_OrmExampleWizard
	 */
	protected $wizard;

	protected function setUp() {
		include_once $this->files->get($this, '/mapper/Wizard.php');
		include_once $this->files->get($this, '/model/Wizard.php');

		\Module\Orm\Factory::clearSources();
		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new \Module\Orm\DataSource\Pdo\Mysql((array)$config->test);

		$this->source->pdo()->beginTransaction();
		\Module\Orm\Factory::addSource('test', $this->source);
		\Module\Orm\Factory::setDefaultSource('test');

		$this->wizard = new \Module\Orm\Test\Classes\Model\OrmExampleWizard();
	}

	public function testExceptionShouldThrowWhenNoTypeField() {
		$this->setExpectedException('\Module\Orm\Exception\IncompletedResource', 'Resource definition is not completed: wizard');
		$this->wizard->addressNoType;
	}

	public function testExceptionShouldThrowWhenUnknownTypeSpecified() {
		$this->setExpectedException('\Module\Orm\Exception\UnknownRelationType', 'Relation addressUnknownType with type some-relation-type is not supported');
		$this->wizard->addressUnknownType;
	}

	public function testExceptionShouldThrowWhenUnknownRelationGetted() {
		$this->setExpectedException('\Module\Orm\Exception\UnknownField', 'Unknown resource field: wizard.unknown relation');
		\Module\Orm\Test\Classes\Model\OrmExampleWizard::mapper()->getResource()->getRelation('unknown relation');
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source);
		\Module\Orm\Factory::clearSources();
	}

}