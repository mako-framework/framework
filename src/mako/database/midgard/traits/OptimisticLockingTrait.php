<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\midgard\Query;
use mako\database\midgard\traits\exceptions\StaleRecordException;
use mako\database\query\Raw;

use function property_exists;

/**
 * Optimistic locking trait.
 */
trait OptimisticLockingTrait
{
	/**
	 * Returns trait hooks.
	 *
	 * @return array
	 */
	protected function getOptimisticLockingTraitHooks(): array
	{
		return
		[
			'beforeInsert' =>
			[
				function(array $values, $query): array
				{
					$lockingColumn = $this->getLockingColumn();

					$this->columns[$lockingColumn] = 1;

					return $values + [$lockingColumn => 1];
				},
			],
			'beforeUpdate' =>
			[
				function(array $values, $query): array
				{
					$lockingColumn = $this->getLockingColumn();

					return $values + [$lockingColumn => new Raw("{$query->getCompiler()->escapeIdentifier($lockingColumn)} + 1")];
				},
			],
		];
	}

	/**
	 * Making sure that cloning returns a "fresh copy" of the record.
	 */
	public function __clone()
	{
		if($this->isPersisted)
		{
			unset($this->columns[$this->getLockingColumn()]);

			parent::__clone();
		}
	}

	/**
	 * Returns the optimistic locking column.
	 *
	 * @return string
	 */
	protected function getLockingColumn(): string
	{
		return property_exists($this, 'lockingColumn') ? $this->lockingColumn : 'lock_version';
	}

	/**
	 * Reloads the record from the database.
	 *
	 * @return bool
	 */
	public function reload(): bool
	{
		if($this->isPersisted)
		{
			$model = static::get($this->getPrimaryKeyValue());

			if($model !== null)
			{
				$this->original = $this->columns = $model->getRawColumnValues();

				$this->related = $model->getRelated();

				return true;
			}
		}

		return false;
	}

	/**
	 * Sets the optimistic locking version.
	 *
	 * @param int $version Locking version
	 */
	public function setLockVersion(int $version): void
	{
		$this->columns[$this->getLockingColumn()] = $version;
	}

	/**
	 * Returns the optimistic locking version.
	 *
	 * @return int
	 */
	public function getLockVersion(): int
	{
		return $this->columns[$this->getLockingColumn()];
	}

	/**
	 * Updates an existing record.
	 *
	 * @param  \mako\database\midgard\Query $query Query builder
	 * @return bool
	 */
	protected function updateRecord(Query $query): bool
	{
		$lockingColumn = $this->getLockingColumn();

		$lockVersion = $this->columns[$lockingColumn]++;

		$query->where($lockingColumn, '=', $lockVersion);

		$result = parent::updateRecord($query);

		if(!$result)
		{
			$this->columns[$lockingColumn]--;

			throw new StaleRecordException('Attempted to update a stale record.');
		}

		return $result;
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @param  \mako\database\midgard\Query $query Query builder
	 * @return bool
	 */
	protected function deleteRecord(Query $query): bool
	{
		$lockingColumn = $this->getLockingColumn();

		$query->where($lockingColumn, '=', $this->columns[$lockingColumn]);

		$deleted = parent::deleteRecord($query);

		if(!$deleted)
		{
			throw new StaleRecordException('Attempted to delete a stale record.');
		}

		return $deleted;
	}
}
