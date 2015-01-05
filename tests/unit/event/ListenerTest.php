<?php

namespace mako\tests\unit\event;

use mako\event\Listener;

/**
 * @group unit
 */

class ListenerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testHas()
	{
		$listener = new Listener;

		$listener->register('foo', function(){});

		$this->assertTrue($listener->has('foo'));

		$this->assertFalse($listener->has('bar'));
	}

	/**
	 * 
	 */

	public function testClearAll()
	{
		$listener = new Listener;

		$listener->register('foo', function(){});

		$listener->register('bar', function(){});

		$this->assertTrue($listener->has('foo'));

		$this->assertTrue($listener->has('bar'));

		$listener->clear();

		$this->assertFalse($listener->has('foo'));

		$this->assertFalse($listener->has('bar'));
	}

	/**
	 * 
	 */

	public function testClearEvent()
	{
		$listener = new Listener;

		$listener->register('foo', function(){});

		$listener->register('bar', function(){});

		$this->assertTrue($listener->has('foo'));

		$this->assertTrue($listener->has('bar'));

		$listener->clear('foo');

		$this->assertFalse($listener->has('foo'));

		$this->assertTrue($listener->has('bar'));
	}

	/**
	 * 
	 */

	public function testTrigger()
	{
		$listener = new Listener;

		$listener->register('foo', function(){ return false; });

		$listener->register('foo', function(){ return 'foo'; });

		$listener->register('foo', function(){ return 'bar'; });

		$this->assertEquals([false, 'foo', 'bar'], $listener->trigger('foo'));
	}

	/**
	 * 
	 */

	public function testTriggerWithParams()
	{
		$listener = new Listener;

		$listener->register('foo', function($num){ return 'foo' . $num; });

		$listener->register('foo', function($num){ return 'bar' . $num; });

		$this->assertEquals(['foo1', 'bar1'], $listener->trigger('foo', [1]));
	}

	/**
	 * 
	 */

	public function testTriggerWithBreak()
	{
		$listener = new Listener;

		$listener->register('foo', function(){ return false; });

		$listener->register('foo', function(){ return 'foo'; });

		$listener->register('foo', function(){ return 'bar'; });

		$this->assertEquals([false], $listener->trigger('foo', [], true));
	}

	/**
	 * 
	 */

	public function testOverride()
	{
		$listener = new Listener;

		$listener->register('foo', function(){ return 'foo'; });

		$listener->register('foo', function(){ return 'bar'; });

		$this->assertEquals(['foo', 'bar'], $listener->trigger('foo'));

		$listener->override('foo', function(){ return 'baz'; });

		$this->assertEquals(['baz'], $listener->trigger('foo'));
	}
}