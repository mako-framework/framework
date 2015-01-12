<?php

namespace mako\tests\unit\cli\input;

use mako\cli\input\Input;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class InputTest extends PHPUnit_Framework_TestCase
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

	public function getReader()
	{
		return m::mock('mako\cli\input\reader\ReaderInterface');
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

		$this->assertSame(['arg1' => 'foo', 'arg2' => 'bar', 'arg3' => 'baz', 'named' => 'baz'], $input->getArguments());
	}

	/**
	 * 
	 */

	public function testGetNumericArgument()
	{
		$reader = $this->getReader();

		$arguments = ['foo', 'bar'];

		$input = new Input($reader, $arguments);

		$this->assertSame('foo', $input->getArgument(1));

		$this->assertSame('foo', $input->getArgument('arg1'));

		$this->assertSame('bar', $input->getArgument(2));

		$this->assertSame('bar', $input->getArgument('arg2'));
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