<?php

namespace Module\Orm;

class Collection implements \SeekableIterator, \Countable, \ArrayAccess {

	protected $mapper;

	protected $data;

	protected $count;

	protected $current;

	public function __construct(\Module\Orm\Mapper $mapper, array $data) {
		$this->mapper = $mapper;
		$this->data   = $data;
		$this->count  = count($data);
		$this->rewind();
	}

	/**
	 * @return boolean Returns true on success or false on failure.
	 * @param mixed $offset
	 */
	public function offsetExists($offset) {
		return (int)$offset < $this->count && (int)$offset >= 0;
	}

	/**
	 * @return mixed Can return all value types.
	 * @param mixed $offset
	 */
	public function offsetGet($offset) {
		$this->seek($offset);
		return $this->current();
	}

	/**
	 * @return void
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value) {
	}

	/**
	 * @return void
	 * @param mixed $offset
	 */
	public function offsetUnset($offset) {
	}

	/**
	 * @return int The custom count as an integer.
	 */
	public function count() {
		return $this->count;
	}

	/**
	 * @return void
	 * @param int $position
	 *
	 * @throws \InvalidArgumentException
	 */
	public function seek($position) {
		if ($this->offsetExists($position)) {
			$this->current = $position;
			return;
		}
		throw new \InvalidArgumentException('Argument should be between 0 and ' . ($this->count - 1));
	}

	/**
	 * @return mixed Can return any type.
	 */
	public function current() {
		if ($this->valid()) {
			$identity = $this->currentIdentity();
			$result   = $this->mapper->runtimeCache()->get($identity);
			if (null === $result) {
				$result = $this->mapper->runtimeCache()->store(
					$this->mapper->load($this->data[$this->current])
				);
			}
			return $result;
		}
		return null;
	}

	/**
	 * @return void Any returned value is ignored.
	 */
	public function next() {
		++$this->current;
	}

	/**
	 * @return int|string Scalar on success, integer 0 on failure.
	 */
	public function key() {
		return $this->current;
	}

	/**
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 */
	public function valid() {
		return $this->current < $this->count;
	}

	/**
	 * @return void Any returned value is ignored.
	 */
	public function rewind() {
		$this->current = 0;
	}

	/**
	 * @return array
	 */
	protected function currentIdentity() {
		$result = array();
		foreach ($this->mapper->getResource()->identity() as $field) {
			$result[$field] = $this->data[$this->current][$field];
		}
		return $result;
	}

}