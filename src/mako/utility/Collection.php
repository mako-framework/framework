<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\utility;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use mako\common\traits\ExtendableTrait;
use OutOfBoundsException;
use ReflectionFunction;
use ReflectionNamedType;

use function array_chunk;
use function array_combine;
use function array_diff_key;
use function array_filter;
use function array_flip;
use function array_intersect_key;
use function array_key_exists;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_pop;
use function array_push;
use function array_shift;
use function array_unshift;
use function array_values;
use function count;
use function shuffle;
use function uasort;
use function usort;
use function vsprintf;

/**
 * Collection.
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate
{
	use ExtendableTrait;

	/**
	 * Constructor.
	 */
	final public function __construct(
		protected array $items = []
	)
	{}

	/**
	 * Returns all the items in the collection.
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * Returns all the values in the collection.
	 */
	public function getValues(): array
	{
		return array_values($this->items);
	}

	/**
	 * Resets the collection keys.
	 *
	 * @return $this
	 */
	public function resetKeys(): static
	{
		$this->items = array_values($this->items);

		return $this;
	}

	/**
	 * Adds a new item to the collection.
	 *
	 * @return $this
	 */
	public function put(int|string $key, mixed $value): static
	{
		$this->items[$key] = $value;

		return $this;
	}

	/**
	 * Returns TRUE if the item key exists and FALSE if not.
	 */
	public function has(int|string $key): bool
	{
		return array_key_exists($key, $this->items);
	}

	/**
	 * Returns an item from the collection.
	 */
	public function get(int|string $key, mixed $default = null): mixed
	{
		if(array_key_exists($key, $this->items))
		{
			return $this->items[$key];
		}

		return $default;
	}

	/**
	 * Removes an item from the collection.
	 *
	 * @return $this
	 */
	public function remove(int|string $key): static
	{
		unset($this->items[$key]);

		return $this;
	}

	/**
	 * Clears the collection.
	 *
	 * @return $this
	 */
	public function clear(): static
	{
		$this->items = [];

		return $this;
	}

	/**
	 * Checks whether or not an offset exists.
	 */
	public function offsetExists(mixed $offset): bool
	{
		return isset($this->items[$offset]);
	}

	/**
	 * Returns the value at the specified offset.
	 */
	public function offsetGet(mixed $offset): mixed
	{
		if(array_key_exists($offset, $this->items))
		{
			return $this->items[$offset];
		}

		throw new OutOfBoundsException(vsprintf('Undefined offset [ %s ].', [$offset]));
	}

	/**
	 * Assigns a value to the specified offset.
	 */
	public function offsetSet(mixed $offset, mixed $value): void
	{
		if($offset === null)
		{
			$this->items[] = $value;
		}
		else
		{
			$this->items[$offset] = $value;
		}
	}

	/**
	 * Unsets an offset.
	 */
	public function offsetUnset(mixed $offset): void
	{
		unset($this->items[$offset]);
	}

	/**
	 * Returns the numner of items in the collection.
	 */
	public function count(): int
	{
		return count($this->items);
	}

	/**
	 * Retruns an array iterator object.
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->items);
	}

	/**
	 * Returns TRUE if the collection is empty and FALSE if not.
	 */
	public function isEmpty(): bool
	{
		return empty($this->items);
	}

	/**
	 * Prepends the passed item to the front of the collection
	 * and returns the new number of elements in the collection.
	 */
	public function unshift(mixed $item): int
	{
		return array_unshift($this->items, $item);
	}

	/**
	 * Shifts the first value of the collection off and returns it,
	 * shortening the collection by one element.
	 */
	public function shift(): mixed
	{
		return array_shift($this->items);
	}

	/**
	 * Pushes the passed variable onto the end of the collection
	 * and returns the new number of elements in the collection.
	 */
	public function push(mixed $item): int
	{
		return array_push($this->items, $item);
	}

	/**
	 * Pops and returns the last value of the collection,
	 * shortening the collection by one element.
	 */
	public function pop(): mixed
	{
		return array_pop($this->items);
	}

	/**
	 * Returns the first item of the collection or NULL if the collection is empty.
	 */
	public function first(): mixed
	{
		$key = array_key_first($this->items);

		if($key === null)
		{
			return null;
		}

		return $this->items[$key];
	}

	/**
	 * Returns the last item of the collection or NULL if the collection is empty.
	 */
	public function last(): mixed
	{
		$key = array_key_last($this->items);

		if($key === null)
		{
			return null;
		}

		return $this->items[$key];
	}

	/**
	 * Sorts the collection using the specified comparator callable
	 * and returns TRUE on success and FALSE on failure.
	 *
	 * @return $this
	 */
	public function sort(callable $comparator, bool $maintainIndexAssociation = true): static
	{
		if($maintainIndexAssociation)
		{
			uasort($this->items, $comparator);
		}
		else
		{
			usort($this->items, $comparator);
		}

		return $this;
	}

	/**
	 * Chunks the collection into a collection containing $size sized collections.
	 */
	public function chunk(int $size): static
	{
		$collections = [];

		foreach(array_chunk($this->items, $size) as $chunk)
		{
			$collections[] = new static($chunk);
		}

		return new static($collections);
	}

	/**
	 * Shuffles the items in the collection.
	 *
	 * @return $this
	 */
	public function shuffle(): static
	{
		shuffle($this->items);

		return $this;
	}

	/**
	 * Applies the callable on all items in the collection.
	 *
	 * @return $this
	 */
	public function each(callable $callable): static
	{
		$reflection = new ReflectionFunction($callable);

		$callableHasVoidReturnType = $reflection->hasReturnType() &&
			$reflection->getReturnType() instanceof ReflectionNamedType &&
			$reflection->getReturnType()->getName() === 'void';

		foreach($this->items as $key => $value)
		{
			if($callableHasVoidReturnType)
			{
				$callable($value, $key);
			}
			else
			{
				$this->items[$key] = $callable($value, $key);
			}
		}

		return $this;
	}

	/**
	 * Returns a new collection where the callable has
	 * been applied to all the items.
	 */
	public function map(callable $callable): static
	{
		$keys = array_keys($this->items);

		$values = array_map($callable, $this->items, $keys);

		return new static(array_combine($keys, $values));
	}

	/**
	 * Returns a new filtered collection.
	 */
	public function filter(?callable $callable = null): static
	{
		if($callable === null)
		{
			return new static(array_filter($this->items));
		}

		return new static(array_filter($this->items, $callable, ARRAY_FILTER_USE_BOTH));
	}

	/**
	 * Returns a new collection where all items not in the provided list have been removed.
	 */
	public function with(array $keys): static
	{
		return new static(array_intersect_key($this->items, array_flip($keys)));
	}

	/**
	 * Returns a new collection where all items in the provided list have been removed.
	 */
	public function without(array $keys): static
	{
		return new static(array_diff_key($this->items, array_flip($keys)));
	}

	/**
	 * Merges two collections.
	 */
	public function merge(Collection $collection): static
	{
		return new static([...$this->items, ...$collection->getItems()]);
	}
}
