<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\database\midgard\traits;

use \mako\database\midgard\traits\StaleRecordException;

/**
 * Optimistic locking trait.
 *
 * @author  Frederic G. Østby
 */

trait OptimisticLockingTrait
{
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

		return false;
	}

	/**
	 * Inserts a new record into the database.
	 * 
	 * @access  protected
	 * @param   \mako\database\midgard\Query  $query  Query builder
	 */

	protected function insertRecord($query)
	{
		$this->columns[$this->getLockingColumn()] = 0;

		parent::insertRecord($query);
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
		$lockVersion = $this->columns[$this->getLockingColumn()]++;

		$query->where($this->getLockingColumn(), '=', $lockVersion);

		$result = parent::updateRecord($query);

		if(!$result)
		{
			$this->columns[$this->getLockingColumn()]--;

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
		$query->where($this->getLockingColumn(), '=', $this->columns[$this->getLockingColumn()]);
		
		$deleted = parent::deleteRecord($query);

		if(!$deleted)
		{
			throw new StaleRecordException(vsprintf("%s(): Attempted to delete a stale record.", [__METHOD__]));
		}

		return $deleted;
	}
}