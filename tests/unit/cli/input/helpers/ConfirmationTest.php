<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Confirmation;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class ConfirmationTest extends TestCase
{
	/**
	 *
	 */
	public function testConfirmationYes(): void
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
	public function testConfirmationNo(): void
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
	public function testConfirmationDefaultNo(): void
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationDefaultYes(): void
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Delete all files? [Y/n] ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'y'));
	}

	/**
	 *
	 */
	public function testConfirmationYesCustom(): void
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
	public function testConfirmationWithInvalidInput(): void
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
