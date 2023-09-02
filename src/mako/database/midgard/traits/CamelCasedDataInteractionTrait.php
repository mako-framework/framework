<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\exceptions\DatabaseException;
use mako\utility\Str;

use function array_key_exists;
use function method_exists;
use function vsprintf;

/**
 * Camel cased data interaction trait.
 */
trait CamelCasedDataInteractionTrait
{
	/**
	 * Sets a raw column value.
	 */
	public function setRawColumnValue(string $name, mixed $value): void
	{
		parent::setRawColumnValue(Str::camelToSnake($name), $value);
	}

	/**
	 * Sets a column value.
	 */
	public function setColumnValue(string $name, mixed $value): void
	{
		$snakeCased = Str::camelToSnake($name);

		$camelCased = Str::snakeToCamel($name);

		$value = $this->cast($snakeCased, $value);

		if(method_exists($this, "{$camelCased}Mutator"))
		{
			$this->columns[$snakeCased] = $this->{"{$camelCased}Mutator"}($value);
		}
		else
		{
			$this->columns[$snakeCased] = $value;
		}
	}

	/**
	 * Assigns the column values to the model.
	 *
	 * @return $this
	 */
	public function assign(array $columns, bool $raw = false, bool $whitelist = true)
	{
		$snakeCased = [];

		foreach($columns as $key => $value)
		{
			$snakeCased[Str::camelToSnake($key)] = $value;
		}

		return parent::assign($snakeCased, $raw, $whitelist);
	}

	/**
	 * Gets a raw column value.
	 */
	public function getRawColumnValue(string $name): mixed
	{
		return $this->columns[Str::camelToSnake($name)];
	}

	/**
	 * Returns a column value.
	 */
	public function getColumnValue(string $name): mixed
	{
		$camelCased = Str::snakeToCamel($name);

		if(method_exists($this, "{$camelCased}Accessor"))
		{
			return $this->{"{$camelCased}Accessor"}($this->columns[Str::camelToSnake($name)]);
		}

		return $this->columns[Str::camelToSnake($name)];
	}

	/**
	 * Gets a column value or relation.
	 */
	public function getValue(string $name): mixed
	{
		if(array_key_exists(Str::camelToSnake($name), $this->columns))
		{
			// It's a database column

			return $this->getColumnValue($name);
		}
		elseif(array_key_exists($name, $this->related))
		{
			// The column is a cached or eagerly loaded relation

			return $this->related[$name];
		}
		elseif($this->isRelation($name))
		{
			// The column is a relation. Lazy load the record(s) and cache them

			return $this->related[$name] = $this->$name()->getRelated();
		}

		// All options have been exhausted so we'll throw an exception

		throw new DatabaseException(vsprintf('Unknown column or relation [ %s ].', [$name]));
	}

	/**
	 * Checks if a column or relation is set using overloading.
	 */
	public function __isset(string $name): bool
	{
		if(isset($this->columns[Str::camelToSnake($name)]) || isset($this->related[$name]))
		{
			return true;
		}

		return $this->isRelation($name) && $this->getValue($name) !== null;
	}

	/**
	 * Unset column value or relation using overloading.
	 */
	public function __unset(string $name): void
	{
		unset($this->columns[Str::camelToSnake($name)], $this->related[$name]);
	}
}
