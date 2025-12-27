<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;

/**
 * Command that runs all outstanding migrations.
 */
#[CommandDescription('Runs all outstanding migrations.')]
#[CommandArguments(
	new NamedArgument('database', 'd', 'Sets which database connection to use', Argument::IS_OPTIONAL),
)]
class Up extends Command
{
	/**
	 * Executes the command.
	 */
	public function execute(): void
	{
		$migrations = $this->getOutstanding();

		if (empty($migrations)) {
			$this->nl();
			$this->write('<green>There are no outstanding migrations.</green>');
			$this->nl();

			return;
		}

		$batch = $this->getQuery()->max('batch') + 1;

		foreach ($migrations as $migration) {
			$this->runMigration($migration, 'up', $batch);
		}

		$this->nl();

		$this->write('Ran the following migrations:' . PHP_EOL);

		$this->outputMigrationList($migrations);

		$this->nl();
	}
}
