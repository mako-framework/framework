<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations\traits;

/**
 * Rollback trait.
 */
trait RollbackTrait
{
	/**
	 * Rolls back n batches.
	 */
	public function rollback(?int $batches = null): void
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
