<?php

namespace Module\Orm\Pdo;

class Connection extends \PDO {

	/**
	 * @return mixed|\PDOStatement
	 * @param string $statement
	 *
	 * @throws \Exception|null
	 */
	public function query($statement) {
		$now       = microTime(true);
		$exception = null;
		$result    = null;

		try {
			$result = call_user_func_array(array($this, 'parent::query'), func_get_args());
		} catch (\Exception $e) {
			$exception = $e;
		}
		if (isSet(\app()->queryLogger)) {
			\app()->queryLogger->writeQuery(microTime(true) - $now, $statement);
		}
		if ($exception) {
			if (isSet(\app()->queryLogger)) {
				\app()->queryLogger->writeError($exception);
			}
			throw $exception;
		}

		return $result;
	}

	/**
	 * @return int|null
	 * @param string $statement
	 *
	 * @throws \Exception|null
	 */
	public function exec($statement) {
		$now       = microTime(true);
		$exception = null;
		$result    = null;
		try {
			$result = parent::exec($statement);
		} catch (\Exception $e) {
			$exception = $e;
		}

		if (isSet(\app()->queryLogger)) {
			\app()->queryLogger->writeQuery(microTime(true) - $now, $statement);
		}
		if ($exception) {
			if (isSet(\app()->queryLogger)) {
				\app()->queryLogger->writeError($exception);
			}
			throw $exception;
		}

		return $result;
	}

}