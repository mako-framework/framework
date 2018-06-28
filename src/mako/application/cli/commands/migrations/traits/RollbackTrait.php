<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations\traits;

use mako\database\query\ResultSet;

/**
 * Rollback trait.
 *
 * @author Frederic G. Østby
 */
trait RollbackTrait
{
	/**
	 * Returns an array of migrations to roll back.
	 *
	 * @param  int                            $batches Number of batches to roll back
	 * @return \mako\database\query\ResultSet
	 */
	protected function getBatch(int $batches): ResultSet
	{
		$query = $this->builder();

		if($batches > 0)
		{
			$query->where('batch', '>', ($this->builder()->max('batch') - $batches));
		}

		return $query->select(['version', 'package'])->orderBy('version', 'desc')->all();
	}

	/**
	 * Rolls back n batches.
	 *
	 * @param int $batches Number of batches to roll back
	 */
	public function rollback(int $batches = 1)
	{
		$migrations = $this->getBatch($batches);

		if($migrations->isEmpty())
		{
			$this->write('<blue>There are no migrations to roll back.</blue>');

			return;
		}

		foreach($migrations as $migration)
		{
			$this->runMigration($migration, 'down');
		}

		$this->write('Rolled back the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations->getItems());
	}
}
