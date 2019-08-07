<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor;

use mako\cli\input\arguments\Argument;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\reactor\Command;
use mako\tests\TestCase;
use Mockery;

// --------------------------------------------------------------------------
// START CLASSES @deprecated 7.0
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
				'optional'    => false,
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

	public function execute(): void
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
	public function testGetDescription(): void
	{
		$input = Mockery::mock(Input::class);

		$output = Mockery::mock(Output::class);

		// @deprecated 7.0

		$command = new Foo($input, $output);

		$this->assertEquals('Command description.', $command->getDescription());

		// @deprecated 7.0

		$command = new Bar($input, $output);

		$this->assertEquals('', $command->getDescription());

		//

		$command = new class ($input, $output) extends Command
		{
			protected $description = 'Command description.';
		};

		$this->assertEquals('Command description.', $command->getDescription());
	}

	/**
	 *
	 */
	public function testGetArguments(): void
	{
		$input = Mockery::mock(Input::class);

		$output = Mockery::mock(Output::class);

		// @deprecated 7.0

		$command = new Foo($input, $output);

		$arguments = $command->getArguments();

		$this->assertTrue(count($arguments) === 2);

		$this->assertInstanceOf(Argument::class, $arguments[0]);

		$this->assertInstanceOf(Argument::class, $arguments[1]);

		$this->assertSame('arg2', $arguments[0]->getName());

		$this->assertSame('--option', $arguments[1]->getName());

		$this->assertSame('Argument description.', $arguments[0]->getDescription());

		$this->assertSame('Option description.', $arguments[1]->getDescription());

		$this->assertTrue($arguments[0]->isOptional());

		$this->assertFalse($arguments[1]->isOptional());

		//

		$command = new class ($input, $output) extends Command
		{

		};

		$this->assertEquals([], $command->getArguments());
	}
}
