<?php

namespace Module\Orm\Pdo;

class Statement extends \PDOStatement {

	/**
	 * @return bool|mixed
	 * @param mixed $parameters
	 *
	 * @throws \Exception|null
	 */
	public function execute($parameters = null) {
		$now       = microTime(true);
		$exception = null;
		$result    = null;

		try {
			$result = call_user_func_array(array($this, 'parent::execute'), func_get_args());
		} catch (\Exception $e) {
			$exception = $e;
		}

		if (isSet(\app()->queryLogger)) {
			\app()->queryLogger->writeQuery(microTime(true) - $now, $this->queryString);
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