<?php

namespace mako\tests\unit\event;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Observable
{
	use \mako\event\ObservableTrait;

	public function notifyFoo()
	{
		return $this->notifyObservers('foo');
	}

	public function notifyFooWithParams()
	{
		return $this->notifyObservers('foo', [1]);
	}

	public function notifyFooWithBreak()
	{
		return $this->notifyObservers('foo', [], true);
	}
}

class Observer1
{
	public function update()
	{
		return 'observer1';
	}
}

class Observer2
{
	public function update()
	{
		return 'observer2';
	}
}


// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ObservableTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	public function testHasObserver()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){});

		$this->assertTrue($observable->hasObserver('foo'));

		$this->assertFalse($observable->hasObserver('bar'));
	}

	/**
	 * 
	 */

	public function testClearAllObservers()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){});

		$observable->attachObserver('bar', function(){});

		$this->assertTrue($observable->hasObserver('foo'));

		$this->assertTrue($observable->hasObserver('bar'));

		$observable->clearObservers();

		$this->assertFalse($observable->hasObserver('foo'));

		$this->assertFalse($observable->hasObserver('bar'));
	}

	/**
	 * 
	 */

	public function testClearEventObservers()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){});

		$observable->attachObserver('bar', function(){});

		$this->assertTrue($observable->hasObserver('foo'));

		$this->assertTrue($observable->hasObserver('bar'));

		$observable->clearObservers('foo');

		$this->assertFalse($observable->hasObserver('foo'));

		$this->assertTrue($observable->hasObserver('bar'));
	}

	/**
	 * 
	 */

	public function testNotify()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){ return false; });

		$observable->attachObserver('foo', function(){ return 'foo'; });

		$observable->attachObserver('foo', function(){ return 'bar'; });

		$observable->attachObserver('foo', '\mako\tests\unit\event\Observer1');

		$observable->attachObserver('foo',  new \mako\tests\unit\event\Observer2);

		$this->assertEquals([false, 'foo', 'bar', 'observer1', 'observer2'], $observable->notifyFoo());
	}

	/**
	 * 
	 */

	public function testNotifyWithParams()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function($num){ return 'foo' . $num; });

		$observable->attachObserver('foo', function($num){ return 'bar' . $num; });

		$this->assertEquals(['foo1', 'bar1'], $observable->notifyFooWithParams());
	}

	/**
	 * 
	 */

	public function testNotifyWithBreak()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){ return false; });

		$observable->attachObserver('foo', function(){ return 'foo'; });

		$observable->attachObserver('foo', function(){ return 'bar'; });

		$this->assertEquals([false], $observable->notifyFooWithBreak());
	}

	/**
	 * 
	 */

	public function testOverrideObservers()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', function(){ return 'foo'; });

		$observable->attachObserver('foo', function(){ return 'bar'; });

		$this->assertEquals(['foo', 'bar'], $observable->notifyFoo('foo'));

		$observable->overrideObservers('foo', function(){ return 'baz'; });

		$this->assertEquals(['baz'], $observable->notifyFoo('foo'));
	}

	/**
	 * 
	 */

	public function testDetachObserverString()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', '\mako\tests\unit\event\Observer1');

		$this->assertEquals(['observer1'], $observable->notifyFoo('foo'));

		$observable->detachObserver('foo', '\mako\tests\unit\event\Observer1');

		$this->assertEquals([], $observable->notifyFoo('foo'));
	}

	/**
	 * 
	 */

	public function testDetachObserverInstance()
	{
		$observable = new Observable;

		$observable->attachObserver('foo', new \mako\tests\unit\event\Observer1);

		$this->assertEquals(['observer1'], $observable->notifyFoo('foo'));

		$observable->detachObserver('foo', new \mako\tests\unit\event\Observer1);

		$this->assertEquals([], $observable->notifyFoo('foo'));
	}
}