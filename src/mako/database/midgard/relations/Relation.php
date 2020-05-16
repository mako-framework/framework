<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\midgard\ORM;
use mako\database\midgard\Query;

use function array_chunk;
use function array_merge;
use function array_shift;
use function array_unique;
use function count;

/**
 * Base relation.
 *
 * @author Frederic G. Østby
 */
abstract class Relation extends Query
{
	/**
	 * Eager load chunk size.
	 *
	 * @var int
	 */
	const EAGER_LOAD_CHUNK_SIZE = 900;

	/**
	 * Parent record.
	 *
	 * @var \mako\database\midgard\ORM
	 */
	protected $parent;

	/**
	 * Foreign key.
	 *
	 * @var string
	 */
	protected $foreignKey = null;

	/**
	 * Are we lazy load related records?
	 *
	 * @var bool
	 */
	protected $lazy = true;

	/**
	 * Constructor.
	 *
	 * @param \mako\database\connections\Connection $connection Database connection
	 * @param \mako\database\midgard\ORM            $parent     Parent model
	 * @param \mako\database\midgard\ORM            $related    Related model
	 * @param string|null                           $foreignKey Foreign key name
	 */
	public function __construct(Connection $connection, ORM $parent, ORM $related, ?string $foreignKey = null)
	{
		parent::__construct($connection, $related);

		$this->parent = $parent;

		$this->foreignKey = $foreignKey;

		if($parent->isPersisted())
		{
			$this->lazyCriterion();
		}
	}

	/**
	 * Returns the foreign key.
	 *
	 * @return string
	 */
	protected function getForeignKey()
	{
		if($this->foreignKey === null)
		{
			$this->foreignKey = $this->parent->getForeignKey();
		}

		return $this->foreignKey;
	}

	/**
	 * Returns the keys used to eagerly load records.
	 *
	 * @param  array $results Result set
	 * @return array
	 */
	protected function keys(array $results)
	{
		$keys = [];

		foreach($results as $result)
		{
			$keys[] = $result->getPrimaryKeyValue();
		}

		return array_unique($keys);
	}

	/**
	 * Sets the criterion used when lazy loading related records.
	 */
	protected function lazyCriterion(): void
	{
		$this->where("{$this->table}.{$this->getForeignKey()}", '=', $this->parent->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @param  array $keys Parent keys
	 * @return $this
	 */
	protected function eagerCriterion(array $keys)
	{
		$this->in("{$this->table}.{$this->getForeignKey()}", $keys);

		return $this;
	}

	/**
	 * Eager loads records in chunks.
	 *
	 * @param  array                            $keys Parent keys
	 * @return \mako\database\midgard\ResultSet
	 */
	protected function eagerLoadChunked(array $keys)
	{
		// Tell the query builder that we're not lazy loading records

		$this->lazy = false;

		// If the number of related records is greater than the max chunk size
		// then we'll load the records in appropriately sized chunks

		if(count($keys) > static::EAGER_LOAD_CHUNK_SIZE)
		{
			$records = [];

			foreach(array_chunk($keys, static::EAGER_LOAD_CHUNK_SIZE) as $chunk)
			{
				$query = clone $this;

				$records = array_merge($records, $query->eagerCriterion($chunk)->all()->getItems());
			}

			return $this->createResultSet($records);
		}

		// The ammount of related records was small enough to be fetched in a single query

		return $this->eagerCriterion($keys)->all();
	}

	/**
	 * Returns a query instance used to build relation count subqueries.
	 *
	 * @return $this
	 */
	protected function getRelationCountQuery()
	{
		$this->whereColumn("{$this->table}.{$this->getForeignKey()}", '=', "{$this->parent->getTable()}.{$this->parent->getPrimaryKey()}");

		return $this;
	}

	/**
	 * Adjusts the query.
	 */
	protected function adjustQuery(): void
	{
		if(!$this->lazy)
		{
			array_shift($this->wheres);
		}
	}

	/**
	 * Returns a single record from the database.
	 *
	 * @return \mako\database\midgard\ORM|null
	 */
	public function first()
	{
		$this->adjustQuery();

		return parent::first();
	}

	/**
	 * Returns a result set from the database.
	 *
	 * @return \mako\database\midgard\ResultSet
	 */
	public function all()
	{
		$this->adjustQuery();

		return parent::all();
	}
}
