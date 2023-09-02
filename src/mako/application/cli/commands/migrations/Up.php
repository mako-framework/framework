<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\cli\input\arguments\Argument;

/**
 * Command that runs all outstanding migrations.
 */
class Up extends Command
{
	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Runs all outstanding migrations.';

	/**
	 * {@inheritDoc}
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

		$batch = $this->getQuery()->max('batch') + 1;

		foreach($migrations as $migration)
		{
			$this->runMigration($migration, 'up', $batch);
		}

		$this->write('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);
	}
}
