<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;
use mako\database\midgard\ORM;
use Override;

/**
 * Has one relation.
 */
class HasOne extends HasOneOrMany
{
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
			$grouped[$related->getRawColumnValue($foreignKey)] = $related;
		}

		foreach ($results as $result) {
			$result->setRelated($relation, $grouped[$result->getPrimaryKeyValue()] ?? null);
		}
	}

	/**
	 * Fetches a related record from the database.
	 */
	#[Override]
	protected function fetchRelated(): ?ORM
	{
		return $this->first();
	}
}
