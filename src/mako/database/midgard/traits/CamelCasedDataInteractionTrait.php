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

/**
 * Camel cased data interaction trait.
 */
trait CamelCasedDataInteractionTrait
{
	/**
	 * Sets a raw column value.
	 *
	 * @param string $name  Column name
	 * @param mixed  $value Column value
	 */
	public function setRawColumnValue(string $name, $value): void
	{
		parent::setRawColumnValue(Str::camelToSnake($name), $value);
	}

	/**
	 * Sets a column value.
	 *
	 * @param string $name  Column name
	 * @param mixed  $value Column value
	 */
	public function setColumnValue(string $name, $value): void
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
	 * @param  array $columns   Column values
	 * @param  bool  $raw       Set raw values?
	 * @param  bool  $whitelist Remove columns that are not in the whitelist?
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
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getRawColumnValue(string $name)
	{
		return $this->columns[Str::camelToSnake($name)];
	}

	/**
	 * Returns a column value.
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getColumnValue(string $name)
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
	 *
	 * @param  string $name Column name
	 * @return mixed
	 */
	public function getValue(string $name)
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
	 *
	 * @param  string $name Column or relation name
	 * @return bool
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
	 *
	 * @param string $name Column name
	 */
	public function __unset(string $name): void
	{
		unset($this->columns[Str::camelToSnake($name)], $this->related[$name]);
	}
}
