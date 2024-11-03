<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Confirmation;
use mako\cli\input\Input;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ConfirmationTest extends TestCase
{
	/**
	 *
	 */
	public function testConfirmationYes(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('y');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationNo(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('n');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationDefaultNo(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Delete all files? [y/N]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(false, $confirmation->ask('Delete all files?'));
	}

	/**
	 *
	 */
	public function testConfirmationDefaultYes(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Delete all files? [Y/n]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'y'));
	}

	/**
	 *
	 */
	public function testConfirmationYesCustom(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('j');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Delete all files? [j/N]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?', 'n', ['j' => true, 'n' => false]));
	}

	/**
	 *
	 */
	public function testConfirmationWithInvalidInput(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('x');
		$input->shouldReceive('read')->once()->andReturn('y');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->twice()->with('Delete all files? [y/N]' . PHP_EOL . '> ');

		$confirmation = new Confirmation($input, $output);

		$this->assertSame(true, $confirmation->ask('Delete all files?'));
	}
}
