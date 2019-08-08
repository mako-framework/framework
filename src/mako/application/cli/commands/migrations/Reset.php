<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\traits\RollbackTrait;
use mako\cli\input\arguments\Argument;

/**
 * Command that rolls back the last batch of migrations.
 *
 * @author Frederic G. Østby
 */
class Reset extends Command
{
	use RollbackTrait;

	/**
	 * {@inheritdoc}
	 */
	protected $description = 'Resets the database schema.';

	/**
	 * {@inheritdoc}
	 */
	public function getArguments(): array
	{
		return
		[
			new Argument('-d|--database', 'Sets which database connection to use', Argument::IS_OPTIONAL),
			new Argument('-f|--force', 'Force the schema reset?', Argument::IS_BOOL),
		];
	}

	/**
	 * Executes the command.
	 *
	 * @param bool $force Force the schema reset?
	 */
	public function execute(bool $force = false): void
	{
		if($force || $this->confirm('<yellow>Are you sure you want to reset your database?</yellow>'))
		{
			$this->nl();

			$this->rollback();

			return;
		}

		$this->nl();

		$this->write('Ok, no action was performed.');
	}
}
