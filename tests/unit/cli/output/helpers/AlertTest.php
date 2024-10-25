<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\helpers;

use mako\cli\Environment;
use mako\cli\output\helpers\Alert;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class AlertTest extends TestCase
{
	/**
	 *
	 */
	public function testRender(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('getWidth')->once()->andReturn(15);

		$output->shouldReceive('getEnvironment')->once()->andReturn($env);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$alert = new Alert($output);

		$expected = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		$this->assertSame($expected, $alert->render('This is just a test!'));
	}

	/**
	 *
	 */
	public function testRenderWithWidth(): void
	{
		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$alert = new Alert($output, 15);

		$expected = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		$this->assertSame($expected, $alert->render('This is just a test!'));
	}

	/**
	 *
	 */
	public function testDraw(): void
	{
		$expected  = '               '
		. PHP_EOL
		. ' This is just  '
		. PHP_EOL
		. ' a test!       '
		. PHP_EOL
		. '               '
		. PHP_EOL;

		/** @var \mako\cli\output\Output|\Mockery\MockInterface $output */
		$output = Mockery::mock(Output::class);

		/** @var \mako\cli\Environment|\Mockery\MockInterface $env */
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('getWidth')->once()->andReturn(15);

		$output->shouldReceive('getEnvironment')->once()->andReturn($env);

		$output->shouldReceive('getFormatter')->andReturn(null);

		$output->shouldReceive('write')->once()->with($expected, Output::STANDARD);

		$alert = new Alert($output);

		$alert->draw('This is just a test!');
	}
}
