<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations\traits;

/**
 * Rollback trait.
 *
 * @author Frederic G. Østby
 */
trait RollbackTrait
{
	/**
	 * Rolls back n batches.
	 *
	 * @param int|null $batches Number of batches to roll back
	 */
	public function rollback(?int $batches = null)
	{
		$migrations = $this->getMigrated($batches);

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
