<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use mako\database\connections\Connection;
use mako\database\exceptions\DatabaseException;
use mako\database\midgard\ORM;
use mako\database\midgard\Query;
use mako\database\midgard\ResultSet;
use Override;

use function array_chunk;
use function array_shift;
use function array_unique;
use function count;

/**
 * Base relation.
 */
abstract class Relation extends Query
{
	/**
	 * Eager load chunk size.
	 */
	protected const int EAGER_LOAD_CHUNK_SIZE = 900;

	/**
	 * Are we lazy load related records?
	 */
	protected bool $lazy = true;

	/**
	 * Constructor.
	 */
	public function __construct(
		Connection $connection,
		protected ORM $origin,
		ORM $model,
		protected ?string $foreignKey = null
	) {
		parent::__construct($connection, $model);

		if ($origin->isPersisted()) {
			$this->lazyCriterion();
		}
	}

	/**
	 * Returns the foreign key name.
	 */
	protected function getForeignKey(): string
	{
		if ($this->foreignKey === null) {
			$this->foreignKey = $this->origin->getForeignKey();
		}

		return $this->foreignKey;
	}

	/**
	 * Returns the keys used to eagerly load records.
	 */
	protected function keys(array $results): array
	{
		$keys = [];

		foreach ($results as $result) {
			$keys[] = $result->getPrimaryKeyValue();
		}

		return array_unique($keys);
	}

	/**
	 * Sets the criterion used when lazy loading related records.
	 */
	protected function lazyCriterion(): void
	{
		$this->where("{$this->table}.{$this->getForeignKey()}", '=', $this->origin->getPrimaryKeyValue());
	}

	/**
	 * Sets the criterion used when eager loading related records.
	 *
	 * @return $this
	 */
	protected function eagerCriterion(array $keys): static
	{
		$this->in("{$this->table}.{$this->getForeignKey()}", $keys);

		return $this;
	}

	/**
	 * Eager loads records in chunks.
	 */
	protected function eagerLoadChunked(array $keys): ResultSet
	{
		// Tell the query builder that we're not lazy loading records

		$this->lazy = false;

		// If the number of related records is greater than the max chunk size
		// then we'll load the records in appropriately sized chunks

		if (count($keys) > static::EAGER_LOAD_CHUNK_SIZE) {
			$records = [];

			foreach (array_chunk($keys, static::EAGER_LOAD_CHUNK_SIZE) as $chunk) {
				$query = clone $this;

				$records = [...$records, ...$query->eagerCriterion($chunk)->all()->getItems()];
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
	protected function getRelationCountQuery(): static
	{
		$this->whereColumn("{$this->table}.{$this->getForeignKey()}", '=', "{$this->origin->getTable()}.{$this->origin->getPrimaryKey()}");

		return $this;
	}

	/**
	 * Adjusts the query.
	 */
	protected function adjustQuery(): void
	{
		if (!$this->lazy) {
			array_shift($this->wheres);
		}
	}

	/**
	 * Returns a single record from the database.
	 */
	#[Override]
	public function first(): ?ORM
	{
		$this->adjustQuery();

		return parent::first();
	}

	/**
	 * Returns a result set from the database.
	 */
	#[Override]
	public function all(): ResultSet
	{
		$this->adjustQuery();

		return parent::all();
	}

	/**
	 * Fetches the related record(s) from the database.
	 */
	abstract protected function fetchRelated(): mixed;

	/**
	 * Returns the related record(s) from the database.
	 */
	public function getRelated(): mixed
	{
		if (!$this->origin->isPersisted()) {
			throw new DatabaseException('Unable to fetch related records for non-persisted models.');
		}

		return $this->fetchRelated();
	}
}
