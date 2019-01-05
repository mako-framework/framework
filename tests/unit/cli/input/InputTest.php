<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input;

use mako\cli\input\Input;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class InputTest extends TestCase
{
	/**
	 *
	 */
	public function getReader()
	{
		return Mockery::mock('mako\cli\input\reader\ReaderInterface');
	}

	/**
	 *
	 */
	public function testRead()
	{
		$reader = $this->getReader();

		$reader->shouldReceive('read')->once()->andReturn('user input');

		$arguments = [];

		$input = new Input($reader, $arguments);

		$this->assertSame('user input', $input->read());
	}

	/**
	 *
	 */
	public function testGetArguments()
	{
		$reader = $this->getReader();

		$arguments = ['foo', 'bar', 'baz', '--named=baz'];

		$input = new Input($reader, $arguments);

		$this->assertSame(['arg0' => 'foo', 'arg1' => 'bar', 'arg2' => 'baz', 'named' => 'baz'], $input->getArguments());
	}

	/**
	 *
	 */
	public function testGetNumericArgument()
	{
		$reader = $this->getReader();

		$arguments = ['foo', 'bar'];

		$input = new Input($reader, $arguments);

		$this->assertSame('foo', $input->getArgument(0));

		$this->assertSame('foo', $input->getArgument('arg0'));

		$this->assertSame('bar', $input->getArgument(1));

		$this->assertSame('bar', $input->getArgument('arg1'));
	}

	/**
	 *
	 */
	public function testGetNamedArgument()
	{
		$reader = $this->getReader();

		$arguments = ['--foo=bar', '--baz=bax'];

		$input = new Input($reader, $arguments);

		$this->assertSame('bar', $input->getArgument('foo'));

		$this->assertSame('bax', $input->getArgument('baz'));
	}

	/**
	 *
	 */
	public function testGetBooleanNamedArgument()
	{
		$reader = $this->getReader();

		$arguments = ['--foo'];

		$input = new Input($reader, $arguments);

		$this->assertSame(true, $input->getArgument('foo'));
	}

	/**
	 *
	 */
	public function testGetMissingArgument()
	{
		$reader = $this->getReader();

		$arguments = ['--foo'];

		$input = new Input($reader, $arguments);

		$this->assertSame(true, $input->getArgument('foo'));

		$this->assertSame(null, $input->getArgument('bar'));

		$this->assertSame(false, $input->getArgument('bar', false));
	}
}
