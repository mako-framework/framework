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
 */
class Down extends Command
{
	use RollbackTrait;

	/**
	 * {@inheritDoc}
	 */
	protected string $description = 'Rolls back the last batch of migrations.';

	/**
	 * {@inheritDoc}
	 */
	public function getArguments(): array
	{
		return [
			new Argument('-b|--batches', 'Number of batches to roll back', Argument::IS_OPTIONAL | Argument::IS_INT),
			new Argument('-d|--database', 'Sets which database connection to use', Argument::IS_OPTIONAL),
		];
	}

	/**
	 * Executes the command.
	 */
	public function execute(int $batches = 1): void
	{
		$this->rollback($batches);
	}
}
