<?php

/** @var \Nano\Application $application */

return array(
	'default' => array(
		'dsn'          => 'mysql://host=localhost;dbname=nano'
		, 'default'    => true
		, 'datasource' => '\Module\Orm\DataSource\Pdo\Mysql'
		, 'username'   => 'user'
		, 'password'   => ''
		, 'log'        => false
	)
	, 'test' => array(
		'dsn'          => 'mysql://host=localhost;dbname=nano_test'
		, 'datasource' => '\Module\Orm\DataSource\Pdo\Mysql'
		, 'username'   => 'user'
		, 'password'   => ''
		, 'log'        => $application->rootDir . DS . 'orm.log'
	)
);
