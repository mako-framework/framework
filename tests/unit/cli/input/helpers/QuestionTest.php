<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Question;
use mako\tests\TestCase;
use Mockery;

/**
 * @group unit
 */
class QuestionTest extends TestCase
{
	/**
	 *
	 */
	public function testQuestion(): void
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('foobar');

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndNullDefault(): void
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn();

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame(null, $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndCustomDefault(): void
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn();

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:', 'foobar'));
	}
}
