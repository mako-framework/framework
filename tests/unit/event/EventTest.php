<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\event;

use mako\event\Event;
use mako\event\EventHandlerInterface;
use mako\tests\TestCase;
use Mockery;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class EventHanler implements EventHandlerInterface
{
	public function handle($foo)
	{
		return $foo;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class EventTest extends TestCase
{
	/**
	 *
	 */
	public function testRegisterAndHas(): void
	{
		$event = new Event;

		$this->assertFalse($event->has('foo'));

		$event->register('foo', function(): void {});

		$this->assertTrue($event->has('foo'));
	}

	/**
	 *
	 */
	public function testEvents(): void
	{
		$event = new Event;

		$this->assertEmpty($event->events());

		$event->register('foo', function(): void {});

		$this->assertSame(['foo'], $event->events());
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$event = new Event;

		$event->register('foo', function(): void {});

		$this->assertTrue($event->has('foo'));

		$event->clear('foo');

		$this->assertFalse($event->has('foo'));
	}

	/**
	 *
	 */
	public function testTrigger(): void
	{
		$event = new Event;

		$event->register('foo', function() { return 'foo'; });

		$event->register('foo', function() { return 'bar'; });

		$event->register('foo', function() { return 'baz'; });

		$this->assertSame(['foo', 'bar', 'baz'], $event->trigger('foo'));
	}

	/**
	 *
	 */
	public function testTriggerWithParams(): void
	{
		$event = new Event;

		$event->register('foo', function($foo) { return 'one' . $foo; });

		$event->register('foo', function($foo) { return 'one' . $foo; });

		$this->assertSame(['onefoo', 'onefoo'], $event->trigger('foo', ['foo']));
	}

	/**
	 *
	 */
	public function testTriggerWithNamedParams(): void
	{
		$event = new Event;

		$event->register('foo', function($foo, $bar) { return 'one' . $foo . $bar; });

		$event->register('foo', function($foo, $bar) { return 'two' . $foo . $bar; });

		$this->assertSame(['onefoobar', 'twofoobar'], $event->trigger('foo', ['bar' => 'bar', 'foo' => 'foo']));
	}

	/**
	 *
	 */
	public function testTriggerWithBreak(): void
	{
		$event = new Event;

		$event->register('foo', function() { return 'foo'; });

		$event->register('foo', function() { return false; });

		$event->register('foo', function() { return 'baz'; });

		$this->assertSame(['foo', false], $event->trigger('foo', [], true));
	}

	/**
	 *
	 */
	public function testOverride(): void
	{
		$event = new Event;

		$event->register('foo', function() { return 'foo'; });

		$this->assertSame(['foo'], $event->trigger('foo'));

		$event->override('foo', function() { return 'bar'; });

		$this->assertSame(['bar'], $event->trigger('foo'));
	}

	/**
	 *
	 */
	public function testContainerWithClosureHandler(): void
	{
		$container = Mockery::mock('mako\syringe\Container');

		$closure = function() { return 'foo'; };

		$container->shouldReceive('call')->once()->with($closure, [])->andReturn('foo');

		$event = new Event($container);

		$event->register('foo', $closure);

		$this->assertSame(['foo'], $event->trigger('foo'));
	}

	/**
	 *
	 */
	public function testContainerWithClassHandler(): void
	{
		$container = Mockery::mock('mako\syringe\Container');

		$handler = new EventHanler;

		$container->shouldReceive('get')->once()->with(EventHanler::class)->andReturn($handler);

		$container->shouldReceive('call')->once()->with([$handler, 'handle'], ['foo'])->andReturn('foo');

		$event = new Event($container);

		$event->register('foo', EventHanler::class);

		$this->assertSame(['foo'], $event->trigger('foo', ['foo']));
	}

	/**
	 *
	 */
	public function testClassHandler(): void
	{
		$event = new Event;

		$event->register('foo', EventHanler::class);

		$this->assertSame(['parameter'], $event->trigger('foo', ['parameter']));
	}
}
