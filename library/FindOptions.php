<?php

namespace Module\Orm;

class FindOptions {

	/**
	 * @var int
	 */
	protected $limitCount = null;

	/**
	 * @var int
	 */
	protected $limitOffset = null;

	/**
	 * @var boolean[]
	 */
	protected $ordering = array();

	/**
	 * @return \Module\Orm\FindOptions
	 */
	public static function create() {
		return new self();
	}

	/**
	 * @return \Module\Orm\FindOptions
	 * @param int $count
	 * @param int $offset
	 */
	public function limit($count, $offset = 0) {
		$this->limitCount  = (int)$count;
		$this->limitOffset = (int)$offset;
		return $this;
	}

	/**
	 * @return \Module\Orm\FindOptions
	 * @param int $pageNumber
	 * @param int $itemsPerPage
	 */
	public function limitPage($pageNumber, $itemsPerPage) {
		return $this->limit($itemsPerPage, ($pageNumber - 1) * $itemsPerPage);
	}

	/**
	 * @return \Module\Orm\FindOptions
	 * @param string $field
	 * @param boolean|null $ascending
	 */
	public function orderBy($field, $ascending = true) {
		$this->ordering[$field] = $ascending;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getLimitCount() {
		return $this->limitCount;
	}

	/**
	 * @return int|null
	 */
	public function getLimitOffset() {
		return $this->limitOffset;
	}

	/**
	 * @return array
	 */
	public function getOrdering() {
		return $this->ordering;
	}

}