<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\traits\RollbackTrait;
use mako\cli\input\arguments\Argument;
use mako\reactor\attributes\Arguments;
use mako\reactor\attributes\Command as CommandAttribute;

/**
 * Command that rolls back the last batch of migrations.
 */
#[CommandAttribute('migration:reset', 'Resets the database schema.')]
#[Arguments(
	new Argument('-d|--database', 'Sets which database connection to use', Argument::IS_OPTIONAL),
	new Argument('-f|--force', 'Force the schema reset?', Argument::IS_BOOL),
)]
class Reset extends Command
{
	use RollbackTrait;

	/**
	 * Executes the command.
	 */
	public function execute(bool $force = false): void
	{
		if ($force || $this->confirm('<yellow>Are you sure you want to reset your database?</yellow>')) {
			$this->nl();

			$this->rollback();

			return;
		}

		$this->nl();

		$this->write('Ok, no action was performed.');
	}
}
