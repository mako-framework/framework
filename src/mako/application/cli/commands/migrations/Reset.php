<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\traits\RollbackTrait;

/**
 * Command that rolls back the last batch of migrations.
 *
 * @author Frederic G. Østby
 */
class Reset extends Command
{
	use RollbackTrait;

	/**
	 * Command information.
	 *
	 * @var array
	 */
	protected $commandInformation =
	[
		'description' => 'Rolls back the last batch of migrations.',
		'options'     =>
		[
			'force' =>
			[
				'optional'    => true,
				'description' => 'Force the schema reset?',
			],
			'database' =>
			[
				'optional'    => true,
				'description' => 'Sets which database connection to use',
			],
		],
	];

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
