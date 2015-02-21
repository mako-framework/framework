<?php

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Confirmation;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class ConfirmationTest extends PHPUnit_Framework_TestCase
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

	public function testConfirmationYes()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('y');

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */

	public function testConfirmationNo()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('n');

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */

	public function testConfirmationDefaultNo()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn(null);

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */

	public function testConfirmationDefaultYes()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn(null);

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [Y/n] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'y'));
	}

	/**
	 *
	 */

	public function testConfirmationYesCustom()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('j');

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [j/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'n', ['j' => true, 'n' => false]));
	}

	/**
	 *
	 */

	public function testConfirmationWithInvalidInput()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('x');
		$input->shouldReceive('read')->once()->andReturn('y');

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->twice()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}
}