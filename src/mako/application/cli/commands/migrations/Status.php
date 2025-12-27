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

use function count;
use function sprintf;

/**
 * Command that checks if there are any outstanding migrations.
 */
#[CommandDescription('Checks if there are any outstanding migrations.')]
#[CommandArguments(
	new NamedArgument('database', 'd', 'Sets which database connection to use', Argument::IS_OPTIONAL),
	new NamedArgument('exit-code', 'e', 'Exits with 1 if there are outstanding migrations and 0 if there are none', Argument::IS_BOOL),
)]
class Status extends Command
{
	/**
	 * Executes the command.
	 */
	public function execute(bool $exitCode = false): int
	{
		$migrations = $this->getOutstanding();

		if (($count = count($migrations)) > 0) {
			$message = $count === 1 ? 'There is <bold>%s</bold> outstanding migration:' : 'There are <bold>%s</bold> outstanding migrations:';

			$this->write(sprintf($message, "<green>{$count}</green>") . PHP_EOL);

			$this->outputMigrationList($migrations);
		}
		else {
			$this->write('<green>There are no outstanding migrations.</green>');
		}

		return ($exitCode && $count > 0) ? static::STATUS_ERROR : static::STATUS_SUCCESS;
	}
}
