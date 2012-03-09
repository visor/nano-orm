<?php

namespace NanoOrm_Module;

require_once __DIR__ . '/TestPdoSource.php';

/**
 * @group library
 * @group orm
 * @group orm-source
 */
class Library_Orm_SqliteSourceTest extends Library_Orm_TestPdoSource {

	/**
	 * @return Orm_DataSource_Pdo
	 */
	protected function createDataSource() {
		$result = new Orm_DataSource_Pdo_Sqlite(array(
			'dsn' => 'sqlite:' . $this->files->get($this, '/database.sqlite')
		));
		$result->pdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return $result;
	}

}