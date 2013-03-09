<?php

namespace Module\Orm\Pdo;

class Logger {

	protected $count = 0;

	protected $time = 0;

	/**
	 * @return void
	 * @param float $time
	 * @param string $statement
	 */
	public function writeQuery($time, $statement) {
		++$this->count;
		$this->time += $time;
		$this->write('[SQL] #' . sprintf('%03d %03.010f', $this->count, $time) . ' ' . $statement);
	}

	/**
	 * @return void
	 * @param string $message
	 */
	public function writeError($message) {
		$this->write('[ERROR] ' . $message);
	}

	/**
	 * @return void
	 */
	public function writeTotals() {
		$this->write('[TOTAL QUERIES] ' . $this->count);
		$this->write('[TOTAL TIME]    ' . sprintf('%03.010f', $this->time));
	}

	/**
	 * @return int
	 */
	public function getQueriesCounted() {
		return $this->count;
	}

	/**
	 * @return float
	 */
	public function getQueriesTime() {
		return $this->time;
	}

	public function write($message) {
		\app()->log->message('[' . date('Y.m.d H:i:s') . '] ' . $message);
	}

}