<?php

namespace mako\tests\unit\event;

use Mockery as m;

use PHPUnit_Framework_TestCase;

use mako\event\Event;
use mako\event\EventHandlerInterface;

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

class EventTest extends PHPUnit_Framework_TestCase
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

	public function testRegisterAndHas()
	{
		$event = new Event;

		$this->assertFalse($event->has('foo'));

		$event->register('foo', function(){});

		$this->assertTrue($event->has('foo'));
	}

	/**
	 *
	 */

	public function testEvents()
	{
		$event = new Event;

		$this->assertEmpty($event->events());

		$event->register('foo', function(){});

		$this->assertSame(['foo'], $event->events());
	}

	/**
	 *
	 */

	public function testClear()
	{
		$event = new Event;

		$event->register('foo', function(){});

		$this->assertTrue($event->has('foo'));

		$event->clear('foo');

		$this->assertFalse($event->has('foo'));
	}

	/**
	 *
	 */

	public function testTrigger()
	{
		$event = new Event;

		$event->register('foo', function(){ return 'foo'; });

		$event->register('foo', function(){ return 'bar'; });

		$event->register('foo', function(){ return 'baz'; });

		$this->assertSame(['foo', 'bar', 'baz'], $event->trigger('foo'));
	}

	/**
	 *
	 */

	public function testTriggerWithParams()
	{
		$event = new Event;

		$event->register('foo', function($foo){ return 'one' . $foo; });

		$event->register('foo', function($foo){ return 'one' . $foo; });

		$this->assertSame(['onefoo', 'onefoo'], $event->trigger('foo', ['foo']));
	}

	/**
	 *
	 */

	public function testTriggerWithNamedParams()
	{
		$event = new Event;

		$event->register('foo', function($foo, $bar){ return 'one' . $foo . $bar; });

		$event->register('foo', function($foo, $bar){ return 'two' . $foo . $bar; });

		$this->assertSame(['onefoobar', 'twofoobar'], $event->trigger('foo', ['bar' => 'bar', 'foo' => 'foo']));
	}

	/**
	 *
	 */

	public function testTriggerWithBreak()
	{
		$event = new Event;

		$event->register('foo', function(){ return 'foo'; });

		$event->register('foo', function(){ return false; });

		$event->register('foo', function(){ return 'baz'; });

		$this->assertSame(['foo', false], $event->trigger('foo', [], true));
	}

	/**
	 *
	 */

	public function testOverride()
	{
		$event = new Event;

		$event->register('foo', function(){ return 'foo'; });

		$this->assertSame(['foo'], $event->trigger('foo'));

		$event->override('foo', function(){ return 'bar'; });

		$this->assertSame(['bar'], $event->trigger('foo'));
	}

	/**
	 *
	 */

	public function testContainerWithClosureHandler()
	{
		$container = m::mock('mako\syringe\Container');

		$closure = function(){ return 'foo'; };

		$container->shouldReceive('call')->once()->with($closure, [])->andReturn('foo');

		$event = new Event($container);

		$event->register('foo', $closure);

		$this->assertSame(['foo'], $event->trigger('foo'));
	}

	/**
	 *
	 */

	public function testContainerWithClassHandler()
	{
		$container = m::mock('mako\syringe\Container');

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

	public function testClassHandler()
	{
		$event = new Event;

		$event->register('foo', EventHanler::class);

		$this->assertSame(['parameter'], $event->trigger('foo', ['parameter']));
	}
}