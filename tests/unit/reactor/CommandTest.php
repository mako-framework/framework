<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\reactor\Command;
use mako\tests\TestCase;
use Mockery;

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
			'arg2' =>
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

class Bar extends Command
{
	protected $isStrict = true;

	protected $commandInformation =
	[

	];
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class CommandTest extends TestCase
{
	/**
	 *
	 */
	public function testIsStrict()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(false);

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$foo = new Foo($input, $output);

		$this->assertFalse($foo->isStrict());

		//

		$bar = new Bar($input, $output);

		$this->assertTrue($bar->isStrict());
	}

	/**
	 *
	 */
	public function testGetCommandDescription()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(false);

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$foo = new Foo($input, $output);

		$this->assertEquals('Command description.', $foo->getCommandDescription());

		//

		$foo = new Bar($input, $output);

		$this->assertEquals('', $foo->getCommandDescription());
	}

	/**
	 *
	 */
	public function testGetCommandArguments()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(false);

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$foo = new Foo($input, $output);

		$this->assertEquals(['arg2' => ['optional' => true, 'description' => 'Argument description.']], $foo->getCommandArguments());

		//

		$foo = new Bar($input, $output);

		$this->assertEquals([], $foo->getCommandOptions());
	}

	/**
	 *
	 */
	public function testGetCommandOptions()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('getArgument')->with('help')->andReturn(false);

		$output = Mockery::mock('mako\cli\output\Output');

		//

		$foo = new Foo($input, $output);

		$this->assertEquals(['option' => ['optional' => true, 'description' => 'Option description.']], $foo->getCommandOptions());

		//

		$foo = new Bar($input, $output);

		$this->assertEquals([], $foo->getCommandOptions());
	}
}
