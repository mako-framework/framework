<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\helpers;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cli\input\helpers\Question;

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
		Mockery::close();
	}

	/**
	 *
	 */
	public function testQuestion()
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
	public function testQuestionWithNoInputAndNullDefault()
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
	public function testQuestionWithNoInputAndCustomDefault()
	{
		$input = Mockery::mock('mako\cli\input\Input');

		$input->shouldReceive('read')->once()->andReturn();

		$output = Mockery::mock('mako\cli\output\Output');

		$output->shouldReceive('write')->once()->with('Username: ');

		$question = new Question($input, $output);

		$this->assertSame('foobar', $question->ask('Username:', 'foobar'));
	}
}
