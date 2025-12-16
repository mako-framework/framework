<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;
use mako\database\connections\Connection;
use mako\database\exceptions\DatabaseException;
use mako\database\midgard\ORM;
use mako\database\midgard\ResultSet;
use mako\database\query\Query;
use mako\database\query\Raw;
use Override;

use function array_combine;
use function array_diff;
use function array_fill;
use function array_is_list;
use function array_keys;
use function array_last;
use function array_shift;
use function count;
use function explode;
use function is_array;
use function sort;
use function str_contains;

/**
 * Many to many relation.
 */
class ManyToMany extends Relation
{
	/**
	 * Junction columns to include in the result.
	 */
	protected array $alongWith = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		Connection $connection,
		ORM $origin,
		ORM $model,
		?string $foreignKey = null,
		protected ?string $junctionTable = null,
		protected ?string $junctionKey = null
	) {
		parent::__construct($connection, $origin, $model, $foreignKey);

		$this->junctionJoin();

		$this->columns = ["{$this->model->getTable()}.*"];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getColumns(): array
	{
		if ($this->lazy) {
			return [...parent::getColumns(), ...$this->alongWith];
		}

		return [...parent::getColumns(), ...$this->alongWith, "{$this->getJunctionTable()}.{$this->getForeignKey()}"];
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function aggregate(string $function, array|Raw|string $column)
	{
		// Empty "alongWith" when performing aggregate queries

		$this->alongWith = [];

		// Execute parent and return results

		return parent::aggregate($function, $column);
	}

	/**
	 * Columns to include with the result.
	 *
	 * @return $this
	 */
	public function alongWith(array $columns): static
	{
		foreach ($columns as $key => $value) {
			if (str_contains($value, '.') === false) {
				$columns[$key] = "{$this->getJunctionTable()}.{$value}";
			}
		}

		$this->alongWith = $columns;

		return $this;
	}

	/**
	 * Returns the the junction table.
	 */
	protected function getJunctionTable(): string
	{
		if ($this->junctionTable === null) {
			$tables = [$this->origin->getTable(), $this->model->getTable()];

			sort($tables);

			if (str_contains($tables[1], '.')) {
				$table = explode('.', $tables[1]);

				$tables[1] = array_last($table);
			}

			$this->junctionTable = "{$tables[0]}_{$tables[1]}";
		}

		return $this->junctionTable;
	}

	/**
	 * Returns the the junction key.
	 */
	protected function getJunctionKey(): string
	{
		if ($this->junctionKey === null) {
			$this->junctionKey = $this->model->getForeignKey();
		}

		return $this->junctionKey;
	}

	/**
	 * Joins the junction table.
	 */
	protected function junctionJoin(): void
	{
		$this->join($this->getJunctionTable(), "{$this->getJunctionTable()}.{$this->getJunctionKey()}", '=', "{$this->model->getTable()}.{$this->model->getPrimaryKey()}");
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function lazyCriterion(): void
	{
		$this->where("{$this->getJunctionTable()}.{$this->getForeignKey()}", '=', $this->origin->getPrimaryKeyValue());
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function eagerCriterion(array $keys): static
	{
		$this->in("{$this->getJunctionTable()}.{$this->getForeignKey()}", $keys);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	protected function getRelationCountQuery(): static
	{
		$this->whereColumn("{$this->getJunctionTable()}.{$this->getForeignKey()}", '=', "{$this->origin->getTable()}.{$this->origin->getPrimaryKey()}");

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their originating records.
	 */
	public function eagerLoad(array &$results, string $relation, ?Closure $criteria, array $includes): void
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if ($criteria !== null) {
			$criteria($this);
		}

		$foreignKey = $this->getForeignKey();

		foreach ($this->eagerLoadChunked($this->keys($results)) as $related) {
			$grouped[$related->getRawColumnValue($foreignKey)][] = $related;

			unset($related->{$foreignKey}); // Unset as it's not a part of the record
		}

		foreach ($results as $result) {
			$result->setRelated($relation, $this->createResultSet($grouped[$result->getPrimaryKeyValue()] ?? []));
		}
	}

	/**
	 * Fetches a related result set from the database.
	 */
	#[Override]
	protected function fetchRelated(): ResultSet
	{
		return $this->all();
	}

	/**
	 * Returns a query builder instance to the junction table.
	 */
	protected function junction(bool $includeWheres = false): Query
	{
		$query = $this->connection->getQuery()->table($this->getJunctionTable());

		if ($includeWheres && count($this->wheres) > 1) {
			$query->wheres = $this->wheres;

			array_shift($query->wheres);
		}

		return $query;
	}

	/**
	 * Returns an array of ids.
	 */
	protected function getJunctionKeys(mixed $id): array
	{
		$ids = [];

		foreach ((is_array($id) ? $id : [$id]) as $value) {
			if ($value instanceof $this->model) {
				$value = $value->getPrimaryKeyValue();
			}

			$ids[] = $value;
		}

		return $ids;
	}

	/**
	 * Get junction attributes.
	 */
	protected function getJunctionAttributes(mixed $key, array $attributes): array
	{
		if (array_is_list($attributes)) {
			if (isset($attributes[$key])) {
				return $attributes[$key];
			}

			return [];
		}

		return $attributes;
	}

	/**
	 * Links related records.
	 */
	public function link(mixed $id, array $attributes = []): bool
	{
		$success = true;

		$foreignKey = $this->getForeignKey();

		$foreignKeyValue = $this->origin->getPrimaryKeyValue();

		$junctionKey = $this->getJunctionKey();

		foreach ($this->getJunctionKeys($id) as $key => $id) {
			$columns = [$foreignKey  => $foreignKeyValue, $junctionKey => $id];

			$success = $success && $this->junction()
			->insert($columns + $this->getJunctionAttributes($key, $attributes));
		}

		return $success;
	}

	/**
	 * Updates junction attributes.
	 */
	public function updateLink(mixed $id, array $attributes): bool
	{
		$success = true;

		$foreignKey = $this->getForeignKey();

		$foreignKeyValue = $this->origin->getPrimaryKeyValue();

		$junctionKey = $this->getJunctionKey();

		foreach ($this->getJunctionKeys($id) as $key => $id) {
			$success = $success && (bool) $this->junction(true)
			->where($foreignKey, '=', $foreignKeyValue)
			->where($junctionKey, '=', $id)
			->update($this->getJunctionAttributes($key, $attributes));
		}

		return $success;
	}

	/**
	 * Unlinks related records.
	 */
	public function unlink(mixed $id = null, array $attributes = []): bool
	{
		$query = $this->junction(true)->where($this->getForeignKey(), '=', $this->origin->getPrimaryKeyValue());

		// If we have no attributes or the attributes are not a list
		// then we can unlink all the related records in one go

		if (empty($attributes) || !array_is_list($attributes)) {
			if ($id !== null) {
				$query->in($this->getJunctionKey(), $this->getJunctionKeys($id));
			}

			if (!empty($attributes)) {
				foreach ($attributes as $column => $value) {
					$query->where($column, '=', $value);
				}
			}

			return (bool) $query->delete();
		}

		// We has a list of attributes so we need to unlink the records one by one

		$success = true;

		$iterable = $id === null ?
		array_combine(array_keys($attributes), array_fill(0, count($attributes), null)) : $this->getJunctionKeys($id);

		foreach ($iterable as $key => $id) {
			$queryClone = clone $query;

			if ($id !== null) {
				$queryClone->where($this->getJunctionKey(), '=', $id);
			}

			foreach ($this->getJunctionAttributes($key, $attributes) as $column => $value) {
				$queryClone->where($column, '=', $value);
			}

			$success = $success && (bool) $queryClone->delete();
		}

		return $success;
	}

	/**
	 * Synchronize related records.
	 */
	public function synchronize(array $ids, array $attributes = []): bool
	{
		if (!empty($attributes) && array_is_list($attributes)) {
			throw new DatabaseException('Synchronize can only be used with a single set of attributes.');
		}

		$success = true;

		$keys = $this->getJunctionKeys($ids);

		// Fetch existing links

		$query = $this->junction()
		->where($this->getForeignKey(), '=', $this->origin->getPrimaryKeyValue())
		->select([$this->getJunctionKey()]);

		foreach ($attributes as $column => $value) {
			$query->where($column, '=', $value);
		}

		$existing = $query->all()
		->pluck($this->getJunctionKey());

		// Link new relations

		if (!empty($diff = array_diff($keys, $existing))) {
			$success = $this->link($diff, $attributes);
		}

		// Unlink old relations

		if (!empty($diff = array_diff($existing, $keys))) {
			$success = $success && $this->unlink($diff, $attributes);
		}

		// Return status

		return $success;
	}
}
