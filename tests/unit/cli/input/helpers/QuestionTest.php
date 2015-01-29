<?php

namespace mako\tests\unit\cli\input\helpers;

use mako\cli\input\helpers\Question;

use Mockery as m;

use PHPUnit_Framework_TestCase;

/**
 * @group unit
 */

class QuestionTest extends PHPUnit_Framework_TestCase
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

	public function testQuestion()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn('foobar');

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:'));
	}

	/**
	 *
	 */

	public function testQuestionWithNoInputAndNullDefault()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn();

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame(null, $question->ask('Username:'));
	}

	/**
	 *
	 */

	public function testQuestionWithNoInputAndCustomDefault()
	{
		$input = m::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn();

		$output = m::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:', 'foobar'));
	}
}