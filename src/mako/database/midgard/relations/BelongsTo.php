<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;
use mako\database\midgard\ORM;

use function array_filter;
use function array_unique;

/**
 * Belongs to relation.
 */
class BelongsTo extends Relation
{
	/**
	 * {@inheritDoc}
	 */
	protected function getForeignKey(): string
	{
		if($this->foreignKey === null)
		{
			$this->foreignKey = $this->model->getForeignKey();
		}

		return $this->foreignKey;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function keys(array $results): array
	{
		$keys = [];

		$foreignKey = $this->getForeignKey();

		foreach($results as $result)
		{
			$keys[] = $result->getRawColumnValue($foreignKey);
		}

		return array_filter(array_unique($keys));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function lazyCriterion(): void
	{
		$this->where("{$this->table}.{$this->model->getPrimaryKey()}", '=', $this->origin->getRawColumnValue($this->getForeignKey()));
	}

	/**
	 * {@inheritDoc}
	 */
	protected function eagerCriterion(array $keys): static
	{
		$this->in("{$this->table}.{$this->model->getPrimaryKey()}", $keys);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function getRelationCountQuery(): static
	{
		$this->whereColumn("{$this->table}.{$this->model->getPrimaryKey()}", '=', "{$this->origin->getTable()}.{$this->getForeignKey()}");

		return $this;
	}

	/**
	 * Eager loads related records and matches them with their originating records.
	 */
	public function eagerLoad(array &$results, string $relation, ?Closure $criteria, array $includes): void
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if(!empty($keys = $this->keys($results)))
		{
			if($criteria !== null)
			{
				$criteria($this);
			}

			foreach($this->eagerLoadChunked($keys) as $related)
			{
				$grouped[$related->getPrimaryKeyValue()] = $related;
			}
		}

		$foreignKey = $this->getForeignKey();

		foreach($results as $result)
		{
			$result->setRelated($relation, $grouped[$result->getRawColumnValue($foreignKey)] ?? null);
		}
	}

	/**
	 * Fetches a related record from the database.
	 */
	protected function fetchRelated(): ?ORM
	{
		if($this->origin->getRawColumnValue($this->getForeignKey()) === null)
		{
			return null;
		}

		return $this->first();
	}
}
