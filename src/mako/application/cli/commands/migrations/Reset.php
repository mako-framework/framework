<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\traits\RollbackTrait;
use mako\cli\input\arguments\Argument;
use mako\cli\input\arguments\NamedArgument;
use mako\reactor\attributes\CommandArguments;
use mako\reactor\attributes\CommandDescription;

/**
 * Command that rolls back the last batch of migrations.
 */
#[CommandDescription('Resets the database schema.')]
#[CommandArguments(
	new NamedArgument('database', 'd', 'Sets which database connection to use', Argument::IS_OPTIONAL),
	new NamedArgument('force', 'f', 'Force the schema reset?', Argument::IS_BOOL),
)]
class Reset extends Command
{
	use RollbackTrait;

	/**
	 * Executes the command.
	 */
	public function execute(bool $force = false): void
	{
		$this->nl();

		if ($force || $this->confirm('Are you sure you want to reset your database?')) {
			$this->rollback();

			return;
		}

		$this->nl();
		$this->write('Ok, no action was performed.');
		$this->nl();
	}
}
