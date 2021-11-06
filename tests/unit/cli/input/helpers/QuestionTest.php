<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Question;
use mako\cli\input\Input;
use mako\cli\output\Output;
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
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn('foobar');

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndNullDefault(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame(null, $question->ask('Username:'));
	}

	/**
	 *
	 */
	public function testQuestionWithNoInputAndCustomDefault(): void
	{
		/** @var \mako\cli\input\Input|\Mockery\MockInterface $input */
		$input = Mockery::mock(Input::class);

		$input->shouldReceive('read')->once()->andReturn();

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:', 'foobar'));
	}
}
