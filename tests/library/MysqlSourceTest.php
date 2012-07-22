<?php

namespace Module\Orm\Test\Cases;

require_once __DIR__ . '/TestPdoSource.php';

/**
 * @group library
 * @group orm
 * @group orm-source
 */
class MysqlSourceTest extends TestPdoSource {

	/**
	 * @var \Module\Orm\DataSource\Pdo\Mysql
	 */
	protected $source;

	/**
	 * @var \Module\Orm\Mapper
	 */
	protected $mapper;

	/**
	 * @return \Module\Orm\DataSource\Pdo
	 */
	protected function createDataSource() {
		$config = $GLOBALS['application']->config->get('orm');
		$result = new \Module\Orm\DataSource\Pdo\Mysql((array)$config->test);
		return $result;
	}

}