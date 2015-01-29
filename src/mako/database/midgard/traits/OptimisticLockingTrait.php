<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use mako\database\midgard\traits\StaleRecordException;
use mako\database\query\Raw;

/**
 * Optimistic locking trait.
 *
 * @author  Frederic G. Østby
 */

trait OptimisticLockingTrait
{
	/**
	 * Returns trait hooks.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function getOptimisticLockingTraitHooks()
	{
		return
		[
			'beforeInsert' =>
			[
				function($values, $query)
				{
					$lockingColumn = $this->getLockingColumn();

					$this->columns[$lockingColumn] = 1;

					return $values + [$lockingColumn => 1];
				},
			],
			'beforeUpdate' =>
			[
				function($values, $query)
				{
					$lockingColumn = $this->getLockingColumn();

					return $values + [$lockingColumn => new Raw($query->getCompiler()->escapeIdentifier($lockingColumn) . ' + 1')];
				},
			],
		];
	}

	/**
	 * Making sure that cloning returns a "fresh copy" of the record.
	 *
	 * @access  public
	 */

	public function __clone()
	{
		if($this->exists)
		{
			unset($this->columns[$this->getLockingColumn()]);

			parent::__clone();
		}
	}

	/**
	 * Returns the optimistic locking column.
	 *
	 * @var string
	 */

	protected function getLockingColumn()
	{
		return isset($this->lockingColumn) ? $this->lockingColumn : 'lock_version';
	}

	/**
	 * Reloads the record from the database.
	 *
	 * @access  public
	 * @return  boolean
	 */

	public function reload()
	{
		if($this->exists)
		{
			$model = static::get($this->getPrimaryKeyValue());

			if($model !== false)
			{
				$this->original = $this->columns = $model->getRawColumns();

				$this->related = $model->getRelated();

				return true;
			}
		}

		return false;
	}

	/**
	 * Sets the optimistic locking version.
	 *
	 * @access  public
	 * @param   int     $version  Locking version
	 */

	public function setLockVersion($version)
	{
		$this->columns[$this->getLockingColumn()] = $version;
	}

	/**
	 * Returns the optimistic locking version.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getLockVersion()
	{
		return $this->columns[$this->getLockingColumn()];
	}

	/**
	 * Updates an existing record.
	 *
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 * @return  boolean
	 */

	protected function updateRecord($query)
	{
		$lockingColumn = $this->getLockingColumn();

		$lockVersion = $this->columns[$lockingColumn]++;

		$query->where($lockingColumn, '=', $lockVersion);

		$result = parent::updateRecord($query);

		if(!$result)
		{
			$this->columns[$lockingColumn]--;

			throw new StaleRecordException(vsprintf("%s(): Attempted to update a stale record.", [__METHOD__]));
		}

		return $result;
	}

	/**
	 * Deletes a record from the database.
	 *
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 * @return  boolean
	 */

	protected function deleteRecord($query)
	{
		$lockingColumn = $this->getLockingColumn();

		$query->where($lockingColumn, '=', $this->columns[$lockingColumn]);

		$deleted = parent::deleteRecord($query);

		if(!$deleted)
		{
			throw new StaleRecordException(vsprintf("%s(): Attempted to delete a stale record.", [__METHOD__]));
		}

		return $deleted;
	}
}