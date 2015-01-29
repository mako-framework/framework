<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\application\cli\commands\migrations;

use mako\application\cli\commands\migrations\Command;
use mako\application\cli\commands\migrations\RollbackTrait;

/**
 * Command that rolls back the last batch of migrations.
 *
 * @author  Frederic G. Østby
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
		'arguments'   => [],
		'options'     =>
		[
			'force' =>
			[
				'optional'    => true,
				'description' => 'Force the schema reset?',
			],
		],
	];

	/**
	 * Executes the command.
	 *
	 * @access  public
	 * @param   boolean  $force  Force the schema reset?
	 */

	public function execute($force = false)
	{
		if($force || $this->confirm('<yellow>Are you sure you want to reset your database?</yellow>'))
		{
			$this->nl();

			$this->rollback(0);

			return;
		}

		$this->nl();

		$this->write('Ok, no action was performed.');
	}
}