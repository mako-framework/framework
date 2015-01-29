<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

/**
 * Rollback trait.
 *
 * @author  Frederic G. Østby
 */

trait RollbackTrait
{
	/**
	 * Returns an array of migrations to roll back.
	 *
	 * @access  protected
	 * @param   int        $batches  Number of batches to roll back
	 * @return  array
	 */

	protected function getBatch($batches)
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
	 * @access  public
	 * @param   string  $batches  Number of batches to roll back
	 */

	public function rollback($batches = 1)
	{
		$migrations = $this->getBatch($batches);

		if(empty($migrations))
		{
			$this->write('<blue>There are no migrations to roll back.</blue>');

			return;
		}

		foreach($migrations as $migration)
		{
			$this->runMigration($migration, 'down');

			$this->builder()->where('version', '=', $migration->version)->delete();
		}

		$this->write('Rolled back the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}
}