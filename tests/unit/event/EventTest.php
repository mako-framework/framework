<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\event;

use mako\event\Event;
use mako\event\EventHandlerInterface;
use mako\syringe\Container;
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

		$event->register('foo', fn() => 'foo');

		$event->register('foo', fn() => 'bar');

		$event->register('foo', fn() => 'baz');

		$this->assertSame(['foo', 'bar', 'baz'], $event->trigger('foo'));
	}

	/**
	 *
	 */
	public function testTriggerWithParams(): void
	{
		$event = new Event;

		$event->register('foo', fn($foo) => 'one' . $foo);

		$event->register('foo', fn($foo) => 'one' . $foo);

		$this->assertSame(['onefoo', 'onefoo'], $event->trigger('foo', ['foo']));
	}

	/**
	 *
	 */
	public function testTriggerWithNamedParams(): void
	{
		$event = new Event;

		$event->register('foo', fn($foo, $bar) => 'one' . $foo . $bar);

		$event->register('foo', fn($foo, $bar) => 'two' . $foo . $bar);

		$this->assertSame(['onefoobar', 'twofoobar'], $event->trigger('foo', ['bar' => 'bar', 'foo' => 'foo']));
	}

	/**
	 *
	 */
	public function testTriggerWithBreak(): void
	{
		$event = new Event;

		$event->register('foo', fn() => 'foo');

		$event->register('foo', fn() => false);

		$event->register('foo', fn() => 'baz');

		$this->assertSame(['foo', false], $event->trigger('foo', [], true));
	}

	/**
	 *
	 */
	public function testOverride(): void
	{
		$event = new Event;

		$event->register('foo', fn() => 'foo');

		$this->assertSame(['foo'], $event->trigger('foo'));

		$event->override('foo', fn() => 'bar');

		$this->assertSame(['bar'], $event->trigger('foo'));
	}

	/**
	 *
	 */
	public function testContainerWithClosureHandler(): void
	{
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

		$closure = fn() => 'foo';

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
		/** @var \mako\syringe\Container|\Mockery\MockInterface $container */
		$container = Mockery::mock(Container::class);

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
