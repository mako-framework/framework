<?php

namespace mako\tests\unit\reactor;

use Mockery as m;

use mako\reactor\Command;

use PHPUnit_Framework_TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Foo extends Command
{
	protected $commandInformation =
	[
		'description' => 'Command description.',
		'options' =>
		[
			'option' =>
			[
				'optional'    => true,
				'description' => 'Option description.',
			],
		],
		'arguments' =>
		[
			'argument' =>
			[
				'optional'    => true,
				'description' => 'Argument description.',
			],
		],
	];

	public function execute()
	{

	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class CommandTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function testHelp()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(true);

		$input->shouldReceive('getArgument')->with(1)->andReturn('foo');

		//

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->times(7)->with(PHP_EOL, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Command:</yellow>', 1);

		$output->shouldReceive('writeLn')->once()->with('php reactor foo', 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Description:</yellow>', 1);

		$output->shouldReceive('writeLn')->once()->with('Command description.', 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Arguments:</yellow>', 1);

$argumentsTable = <<<EOF
-----------------------------------------------
| Name     | Description           | Optional |
-----------------------------------------------
| argument | Argument description. | true     |
-----------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($argumentsTable, 1);

		$output->shouldReceive('writeLn')->once()->with('<yellow>Options:</yellow>', 1);

$optionsTable = <<<EOF
-------------------------------------------
| Name   | Description         | Optional |
-------------------------------------------
| option | Option description. | true     |
-------------------------------------------

EOF;

		$output->shouldReceive('write')->once()->with($optionsTable, 1);

		//

		$command = new Foo($input, $output);

		$this->assertFalse($command->shouldExecute());
	}

	/**
	 *
	 */

	public function testExecute()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(false);

		//

		$output = m::mock('mako\cli\output\Output');

		//

		$command = new Foo($input, $output);

		$this->assertTrue($command->shouldExecute());

		$command->execute();
	}
}