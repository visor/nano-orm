<?php

namespace Module\Orm\Test\Cases;

require_once __DIR__ . '/TestPdoSource.php';

/**
 * @group library
 * @group orm
 * @group orm-source
 */
class SqliteSourceTest extends TestPdoSource {

	/**
	 * @return \Module\Orm\DataSource\Pdo
	 */
	protected function createDataSource() {
		$result = new \Module\Orm\DataSource\Pdo\Sqlite(array(
			'dsn' => 'sqlite:' . $this->files->get($this, '/database.sqlite')
		));
		$result->pdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return $result;
	}

}