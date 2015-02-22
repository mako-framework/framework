<?php

namespace mako\tests\unit\syringe;

use mako\syringe\Container;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Foo
{
	public $stdClass;

	public function __construct(\StdClass $stdClass)
	{
		$this->stdClass = $stdClass;
	}
}

class Bar
{
	public $foo;
	public $bar;

	public function __construct($foo = 123, $bar = 456)
	{
		$this->foo = $foo;
		$this->bar = $bar;
	}
}

interface StoreInterface
{

}

class Store implements StoreInterface
{

}

class Baz
{
	public $store;

	public function __construct(StoreInterface $store)
	{
		$this->store = $store;
	}
}

class Baq
{
	public $baq;

	public function setBaq($baq = 123)
	{
		$this->baq = $baq;
	}
}

class Fox
{
	public function __construct($bax)
	{

	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ContainerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testBasic()
	{
		$container = new Container;

		$foo = $container->get('mako\tests\unit\syringe\Foo');

		$this->assertInstanceOf('\StdClass', $foo->stdClass);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\syringe\Container::resolveParameter(): Unable to resolve the [ $bax ] parameter of [ mako\tests\unit\syringe\Fox::__construct ].
	 */

	public function testClassInstantiationWithUnresolvableParameters()
	{
		$container = new Container;

		$foo = $container->get('mako\tests\unit\syringe\Fox');
	}

	/**
	 *
	 */

	public function testParametersFromReflection()
	{
		$container = new Container;

		$bar = $container->get('mako\tests\unit\syringe\Bar');

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(456, $bar->bar);
	}

	/**
	 *
	 */

	public function testNumericParameters()
	{
		$container = new Container;

		$bar = $container->get('mako\tests\unit\syringe\Bar', ['abc', 'def']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);

		//

		$container = new Container;

		$bar = $container->get('mako\tests\unit\syringe\Bar', [1 => 'def', 0 => 'abc']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);
	}

	/**
	 *
	 */

	public function testAssociativeParameters()
	{
		$container = new Container;

		$bar = $container->get('mako\tests\unit\syringe\Bar', ['bar' => 789]);

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(789, $bar->bar);
	}

	/**
	 *
	 */

	public function testMixedParameters()
	{
		$container = new Container;

		$bar = $container->get('mako\tests\unit\syringe\Bar', ['bar' => 'def', 0 => 'abc']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);
	}

	/**
	 *
	 */

	public function testImplementationInjection()
	{
		$container = new Container;

		$container->register('mako\tests\unit\syringe\StoreInterface', 'mako\tests\unit\syringe\Store');

		$baz = $container->get('mako\tests\unit\syringe\Baz');

		$this->assertInstanceOf('mako\tests\unit\syringe\Store', $baz->store);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\syringe\Container::reflectionFactory(): Unable create a [ mako\tests\unit\syringe\StoreInterface ] instance.
	 */

	public function testInterfaceInstantiation()
	{
		$container = new Container;

		$baz = $container->get('mako\tests\unit\syringe\StoreInterface');
	}

	/**
	 *
	 */

	public function testGetUsingAlias()
	{
		$container = new Container;

		$container->register(['mako\tests\unit\syringe\Foo', 'foo'], 'mako\tests\unit\syringe\Foo');

		$foo = $container->get('foo');

		$this->assertInstanceOf('mako\tests\unit\syringe\Foo', $foo);
	}

	/**
	 *
	 */

	public function testRegisterClosure()
	{
		$container = new Container;

		$container->register(['mako\tests\unit\syringe\Bar', 'bar'], function()
		{
			return new Bar('uvw', 'xyz');
		});

		$bar = $container->get('bar');

		$this->assertInstanceOf('mako\tests\unit\syringe\Bar', $bar);

		$this->assertEquals('uvw', $bar->foo);
		$this->assertEquals('xyz', $bar->bar);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\syringe\Container::closureFactory(): The factory closure must return an object.
	 */

	public function testRegisterClosureWithoutReturnValue()
	{
		$container = new Container;

		$container->register(['mako\tests\unit\syringe\Bar', 'bar'], function(){});

		$bar = $container->get('bar');
	}

	/**
	 *
	 */

	public function testRegisterInstance()
	{
		$container = new Container;

		$baq = new Baq;

		$baq->setBaq('foobar');

		$container->registerInstance(['mako\tests\unit\syringe\Baq', 'baq'], $baq);

		$baq = $container->get('baq');

		$this->assertInstanceOf('mako\tests\unit\syringe\Baq', $baq);

		$this->assertSame('foobar', $baq->baq);
	}

	/**
	 *
	 */

	public function testHas()
	{
		$container = new Container;

		$container->register(['mako\tests\unit\syringe\Foo', 'foo'], 'mako\tests\unit\syringe\Foo');

		$this->assertTrue($container->has('mako\tests\unit\syringe\Foo'));

		$this->assertTrue($container->has('foo'));

		$this->assertFalse($container->has('mako\tests\unit\syringe\Bar'));

		$this->assertFalse($container->has('bar'));
	}

	/**
	 *
	 */

	public function testRegisterSingleton()
	{
		$container = new Container;

		$container->registerSingleton(['mako\tests\unit\syringe\Bar', 'bar'], function()
		{
			return new Bar(uniqid(), uniqid());
		});

		$this->assertEquals($container->get('bar'), $container->get('bar'));

		$this->assertNotEquals($container->get('bar'), $container->getFresh('bar'));
	}

	/**
	 *
	 */

	public function testCallClosure()
	{
		$closure = function(Bar $bar)
		{
			return $bar;
		};

		$container = new Container;

		$returnValue = $container->call($closure);

		$this->assertInstanceOf('mako\tests\unit\syringe\Bar', $returnValue);

		//

		$closure = function(Bar $bar, $foo = 123)
		{
			return [$bar, $foo];
		};

		$container = new Container;

		$returnValue = $container->call($closure);

		$this->assertInstanceOf('mako\tests\unit\syringe\Bar', $returnValue[0]);

		$this->assertSame(123, $returnValue[1]);

		//

		$closure = function(Bar $bar, $foo = 123)
		{
			return [$bar, $foo];
		};

		$container = new Container;

		$returnValue = $container->call($closure, ['foo' => 456]);

		$this->assertInstanceOf('mako\tests\unit\syringe\Bar', $returnValue[0]);

		$this->assertSame(456, $returnValue[1]);
	}

	/**
	 *
	 */

	public function testCallMethod()
	{
		$baq = new Baq;

		$container = new Container;

		$container->call([$baq, 'setBaq']);

		$this->assertSame(123, $baq->baq);

		//

		$baq = new Baq;

		$container = new Container;

		$container->call([$baq, 'setBaq'], [456]);

		$this->assertSame(456, $baq->baq);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\syringe\Container::resolveParameter(): Unable to resolve the [ $foo ] parameter of
	 *
	 * The entire exception message isn't included in the test because of some HHVM incompatibility that causes the test to fail
	 */

	public function testCallMethodWithUnresolvableParameters()
	{
		$container = new Container;

		$container->call(function($foo){});
	}
}