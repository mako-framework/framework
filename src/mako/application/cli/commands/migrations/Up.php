<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\cli\input\arguments\Argument;

/**
 * Command that runs all outstanding migrations.
 *
 * @author Frederic G. Østby
 */
class Up extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Runs all outstanding migrations.';

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-d|--database', 'Sets which database connection to use', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Executes the command.
	 */
	public function execute(): void
	{
		$migrations = $this->getOutstanding();

		if(empty($migrations))
		{
			$this->write('<blue>There are no outstanding migrations.</blue>');

			return;
		}

		$batch = $this->builder()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->runMigration($migration, 'up', $batch);
		}

		$this->write('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}
}
