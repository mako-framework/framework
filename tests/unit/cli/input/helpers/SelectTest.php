<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use Mockery;

use mako\cli\input\helpers\Select;
use mako\tests\TestCase;

/**
 * @group unit
 */
class SelectTest extends TestCase
{
	/**
	 *
	 */
	public function testSelectAndPickFirstOption()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('1');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with('1) Burgers' . PHP_EOL . '2) Sushi' . PHP_EOL . '> ');

		$select = new Select($input, $output);

		$this->assertSame(0, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}

	/**
	 *
	 */
	public function testSelectAndPickSecondOption()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('2');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('writeLn')->once()->with('Favorite food?');

		$output->shouldReceive('write')->once()->with('1) Burgers' . PHP_EOL . '2) Sushi' . PHP_EOL . '> ');

		$select = new Select($input, $output);

		$this->assertSame(1, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}

	/**
	 *
	 */
	public function testSelectAndPickFirstOptionAfterPickingInvalidOption()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('3');

		$input->shouldReceive('read')->once()->andReturn('1');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('writeLn')->twice()->with('Favorite food?');

		$output->shouldReceive('write')->twice()->with('1) Burgers' . PHP_EOL . '2) Sushi' . PHP_EOL . '> ');

		$select = new Select($input, $output);

		$this->assertSame(0, $select->ask('Favorite food?', ['Burgers', 'Sushi']));
	}
}
