<?php

namespace MageArab\MegaFramework\Utils;

use MageArab\MegaFramework\Exceptions;
use MageArab\MegaFramework\Traits;


/**
 * Provides the base class for a generic list (items can be accessed by index).
 */
class ArrayList implements \ArrayAccess, \Countable, \IteratorAggregate
{
	use Traits\SmartObject;

	private $list = [];


	/**
	 * Returns an iterator over all items.
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->list);
	}


	/**
	 * Returns items count.
	 */
	public function count(): int
	{
		return count($this->list);
	}


    /**
     * Replaces or appends a item.
     * @param  int|null $index
     * @param $value
     */
	public function offsetSet($index, $value): void
	{
		if ($index === null) {
			$this->list[] = $value;

		} elseif (!is_int($index) || $index < 0 || $index >= count($this->list)) {
			throw new Exceptions\OutOfRangeException('Offset invalid or out of range');

		} else {
			$this->list[$index] = $value;
		}
	}


	/**
	 * Returns a item.
	 * @param  int  $index
	 * @return mixed
	 * @throws Exceptions\OutOfRangeException
	 */
	public function offsetGet($index)
	{
		if (!is_int($index) || $index < 0 || $index >= count($this->list)) {
			throw new Exceptions\OutOfRangeException('Offset invalid or out of range');
		}
		return $this->list[$index];
	}


    /**
     * Determines whether a item exists.
     * @param  int $index
     * @return bool
     */
	public function offsetExists($index): bool
	{
		return is_int($index) && $index >= 0 && $index < count($this->list);
	}


	/**
	 * Removes the element at the specified position in this list.
	 * @param  int  $index
	 * @throws Exceptions\OutOfRangeException
	 */
	public function offsetUnset($index): void
	{
		if (!is_int($index) || $index < 0 || $index >= count($this->list)) {
			throw new Exceptions\OutOfRangeException('Offset invalid or out of range');
		}
		array_splice($this->list, $index, 1);
	}


    /**
     * Prepends a item.
     * @param $value
     */
	public function prepend($value): void
	{
		$first = array_slice($this->list, 0, 1);
		$this->offsetSet(0, $value);
		array_splice($this->list, 1, 0, $first);
	}
}
