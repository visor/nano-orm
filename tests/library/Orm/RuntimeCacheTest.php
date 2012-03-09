<?php

namespace NanoOrm_Module;

/**
 * @group library
 * @group orm
 */
class Library_Orm_RuntimeCacheTest extends \TestUtils_TestCase {

	/**
	 * @var Orm_DataSource_Pdo
	 */
	protected $source;

	/**
	 * @var Mapper_Library_Orm_Example_Address
	 */
	protected $mapper;

	protected function setUp() {
		include_once $this->files->get($this, '/model/Address.php');
		include_once $this->files->get($this, '/mapper/Address.php');

		Orm::clearSources();
		$config       = $GLOBALS['application']->config->get('orm');
		$this->source = new Orm_DataSource_Pdo_Mysql((array)$config->test);

		Orm::addSource('test', $this->source);
		Orm::setDefaultSource('test');

		$this->source->pdo()->beginTransaction();
		$this->mapper = Library_Orm_Example_Address::mapper();
	}

	public function testGetShouldReturnSameResultsForSameIdetities() {
		$id     = $this->insert(array('location' => 'Number 4, Privet Drive'));
		$model1 = $this->mapper->get($id);
		$model2 = $this->mapper->get($id);

		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $model1);
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $model2);
		self::assertSame($model1, $model2);
	}

	public function testAfterInsertingNewRecordGetShouldReturnItWhenRequested() {
		$model1 = new Library_Orm_Example_Address();
		$model1->location = 'Number 4, Privet Drive';
		$model1->save();

		$model2 = $this->mapper->get($model1->id);
		self::assertInstanceOf('NanoOrm_Module\Library_Orm_Example_Address', $model2);
		self::assertSame($model1, $model2);
	}

	public function testFindShouldStoreModelsIntoCache() {
		$this->insert(array('location' => 'Number 4, Privet Drive'));
		$this->insert(array('location' => 'The Burrow'));
		$this->insert(array('location' => 'Game Hut at Hogwarts'));

		$models = $this->mapper->find();
		self::assertEquals(3, count($models));
		foreach ($models as /** @var Orm_Model $model */ $model) {
			self::assertSame($model, $this->mapper->get($model->identity()));
		}
	}

	protected function insert(array $data) {
		$sqlFields = array();
		$sqlValues = array();
		foreach ($data as $field => $value) {
			$sqlFields[] = '`' . $field . '`';
			$sqlValues[] = null === $value ? 'null' : $this->source->pdo()->quote($value);
		}

		$this->source->pdo()->exec(
			'insert into `' . $this->mapper->getResource()->name() . '`(' . implode(', ', $sqlFields) . ') values (' . implode(', ', $sqlValues) . ')'
		);

		return (int)$this->source->pdo()->lastInsertId();
	}

	protected function tearDown() {
		$this->source->pdo()->rollBack();
		unSet($this->source, $this->mapper);
		Orm::clearSources();
	}

}