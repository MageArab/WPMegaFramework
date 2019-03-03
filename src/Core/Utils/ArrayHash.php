<?php

namespace MageArab\MegaFramework\Utils;

use MageArab\MegaFramework\Exceptions;

/**
 * Provides objects to work as array.
 */
class ArrayHash extends \stdClass implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * @param array $arr
     * @param bool $recursive
     * @return static
     */
	public static function from(array $arr, bool $recursive = true)
	{
		$obj = new static;
		foreach ($arr as $key => $value) {
			if ($recursive && is_array($value)) {
				$obj->$key = static::from($value, true);
			} else {
				$obj->$key = $value;
			}
		}
		return $obj;
	}


	/**
	 * Returns an iterator over all items.
	 */
	public function getIterator(): \RecursiveArrayIterator
	{
		return new \RecursiveArrayIterator((array) $this);
	}


	/**
	 * Returns items count.
	 */
	public function count(): int
	{
		return count((array) $this);
	}


    /**
     * Replaces or appends a item.
     * @param $key
     * @param $value
     */
	public function offsetSet($key, $value): void
	{
		if (!is_scalar($key)) { // prevents null
			throw new Exceptions\InvalidArgumentException(sprintf('Key must be either a string or an integer, %s given.', gettype($key)));
		}
		$this->$key = $value;
	}


    /**
     * Returns a item.
     * @param $key
     * @return mixed
     */
	public function offsetGet($key)
	{
		return $this->$key;
	}


    /**
     * Determines whether a item exists.
     * @param $key
     * @return bool
     */
	public function offsetExists($key): bool
	{
		return isset($this->$key);
	}


    /**
     * Removes the element from this list.
     * @param $key
     */
	public function offsetUnset($key): void
	{
		unset($this->$key);
	}
}
