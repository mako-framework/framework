<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input;

use mako\cli\input\arguments\ArgvParser;
use mako\cli\input\Input;
use mako\cli\input\reader\ReaderInterface;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class InputTest extends TestCase
{
	/**
	 *
	 */
	public function getReader(): MockInterface&ReaderInterface
	{
		return Mockery::mock(ReaderInterface::class);
	}

	/**
	 *
	 */
	public function testIsInteractive(): void
	{
		$reader = $this->getReader();

		$arguments = Mockery::mock(ArgvParser::class);

		$input = new Input($reader, $arguments);

		$this->assertTrue($input->isInteractive());
	}

	/**
	 *
	 */
	public function testToggleInteractivity(): void
	{
		$reader = $this->getReader();

		$arguments = Mockery::mock(ArgvParser::class);

		$input = new Input($reader, $arguments);

		$input->makeNonInteractive();

		$this->assertFalse($input->isInteractive());

		$input->makeInteractive();

		$this->assertTrue($input->isInteractive());
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$reader = $this->getReader();

		$reader->shouldReceive('read')->once()->andReturn('user input');

		$arguments = Mockery::mock(ArgvParser::class);

		$input = new Input($reader, $arguments);

		$this->assertSame('user input', $input->read());
	}

	/**
	 *
	 */
	public function testGetArgumentParser(): void
	{
		$reader = $this->getReader();

		$arguments = Mockery::mock(ArgvParser::class);

		$input = new Input($reader, $arguments);

		$this->assertInstanceOf(ArgvParser::class, $input->argumentParser);
		$this->assertInstanceOf(ArgvParser::class, $input->getArgumentParser());
	}

	/**
	 *
	 */
	public function testGetArguments(): void
	{
		$reader = $this->getReader();

		$arguments = Mockery::mock(ArgvParser::class);

		$arguments->shouldReceive('parse')->once()->andReturn(['foo' => 'bar']);

		$input = new Input($reader, $arguments);

		$this->assertSame(['foo' => 'bar'], $input->getArguments());
	}

	/**
	 *
	 */
	public function testGetArgument(): void
	{
		$reader = $this->getReader();

		$arguments = Mockery::mock(ArgvParser::class);

		$arguments->shouldReceive('getArgumentValue')->once()->with('name', 'default')->andReturn('value');

		$input = new Input($reader, $arguments);

		$this->assertSame('value', $input->getArgument('name', 'default'));
	}
}
