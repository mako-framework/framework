<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\cli\input\arguments\Argument;

use function count;
use function vsprintf;

/**
 * Command that checks if there are any outstanding migrations.
 */
class Status extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Checks if there are any outstanding migrations.';

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-d|--database', 'Sets which database connection to use', Argument::IS_OPTIONAL),
			new Argument('-e|--exit-code', 'Exits with 1 if there are outstanding migrations and 0 if there are none', Argument::IS_BOOL),
		];
	}

	/**
	 * Executes the command.
	 *
	 * @param  bool $exitCode Override exit code?
	 * @return int
	 */
	public function execute($exitCode = false): int
	{
		$migrations = $this->getOutstanding();

		if(($count = count($migrations)) > 0)
		{
			$message = $count === 1 ? 'There is %s outstanding migration:' : 'There are %s outstanding migrations:';

			$this->write(vsprintf($message, ["<yellow>{$count}</yellow>"]) . PHP_EOL);

			$this->outputMigrationList($migrations);
		}
		else
		{
			$this->write('<green>There are no outstanding migrations.</green>');
		}

		return ($exitCode && $count > 0) ? static::STATUS_ERROR : static::STATUS_SUCCESS;
	}
}
