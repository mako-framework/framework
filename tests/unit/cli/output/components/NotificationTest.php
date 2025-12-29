<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\components;

use mako\cli\Environment;
use mako\cli\output\components\Notification;
use mako\cli\output\Output;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class NotificationTest extends TestCase
{
	/**
	 *
	 */
	public function testNotificationWithAnsiSupport(): void
	{
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->once()->andReturn(true);

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->once()->with("\x1b]777;notify;Title:foobar;Body:foobar\x07");

		(function () use ($env): void {
			$this->formatter = null;
			$this->environment = $env;
		})->bindTo($output, Output::class)();

		$notification = new Notification($output);

		$notification->notify('Title;foobar', 'Body;foobar');
	}

	/**
	 *
	 */
	public function testNotificationWithoutAnsiSupport(): void
	{
		$env = Mockery::mock(Environment::class);

		$env->shouldReceive('hasAnsiSupport')->once()->andReturn(false);

		$output = Mockery::mock(Output::class);

		$output->shouldReceive('write')->never();

		(function () use ($env): void {
			$this->formatter = null;
			$this->environment = $env;
		})->bindTo($output, Output::class)();

		$notification = new Notification($output);

		$notification->notify('Title;foobar', 'Body;foobar');
	}
}
