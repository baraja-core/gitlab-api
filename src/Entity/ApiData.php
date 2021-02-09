<?php

declare(strict_types=1);

namespace Baraja\GitLabApi\Entity;


use Baraja\GitLabApi\GitLabApiException;

class ApiData extends \stdClass implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	 * @param mixed[] $arr
	 */
	public static function from(array $arr, bool $recursive = true): self
	{
		$obj = new self;
		foreach ($arr as $key => $value) {
			if ($recursive && is_array($value)) {
				$obj->$key = static::from($value);
			} else {
				$obj->$key = $value;
			}
		}

		return $obj;
	}


	/** Returns an iterator over all items. */
	public function getIterator(): \RecursiveArrayIterator
	{
		return new \RecursiveArrayIterator((array) $this);
	}


	/** Returns items count. */
	public function count(): int
	{
		return count((array) $this);
	}


	/**
	 * Replaces or appends a item.
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @throws GitLabApiException
	 */
	public function offsetSet($key, $value): void
	{
		if (!is_scalar($key)) { // prevents null
			throw new GitLabApiException('Key must be either a string or an integer, "' . gettype($key) . '" given.');
		}
		$this->$key = $value;
	}


	/**
	 * Returns a item.
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function offsetGet($key)
	{
		return $this->$key;
	}


	/**
	 * Determines whether a item exists.
	 *
	 * @param mixed $key
	 */
	public function offsetExists($key): bool
	{
		return isset($this->$key);
	}


	/**
	 * Removes the element from this list.
	 *
	 * @param mixed $key
	 */
	public function offsetUnset($key): void
	{
		unset($this->$key);
	}
}
