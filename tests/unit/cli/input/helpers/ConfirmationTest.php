<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\input\helpers\Confirmation;

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
		Mockery::close();
	}

	/**
	 *
	 */
	public function testConfirmationYes()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('y');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationNo()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('n');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationDefaultNo()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn(null);

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationDefaultYes()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn(null);

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [Y/n] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'y'));
	}

	/**
	 *
	 */
	public function testConfirmationYesCustom()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('j');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [j/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'n', ['j' => true, 'n' => false]));
	}

	/**
	 *
	 */
	public function testConfirmationWithInvalidInput()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('x');
		$input->shouldReceive('read')->once()->andReturn('y');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->twice()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}
}