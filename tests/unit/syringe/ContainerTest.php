<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\syringe;

use mako\syringe\Container;
use mako\tests\TestCase;
use stdClass;

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

interface ContextualInterface
{

}

class ContextualImplementationA implements ContextualInterface
{

}

class ContextualImplementationB implements ContextualInterface
{

}

class ContextClassA
{
	public $implementation;

	public function __construct(ContextualInterface $implementation)
	{
		$this->implementation = $implementation;
	}
}

class ContextClassB
{
	public $implementation;

	public function __construct(ContextualInterface $implementation)
	{
		$this->implementation = $implementation;
	}
}

class ReplaceA
{
	protected $value;

	public function __construct($value)
	{
		$this->value = $value;
	}

	public function getValue()
	{
		return $this->value;
	}
}

class ReplaceB
{
	protected $replaceA;

	public function __construct(ReplaceA $replaceA)
	{
		$this->replaceA = $replaceA;
	}

	public function setReplaceA(ReplaceA $replaceA)
	{
		$this->replaceA = $replaceA;
	}

	public function getReplaceAValue()
	{
		return $this->replaceA->getValue();
	}
}

function syringeFunction($foo = 123, $bar = 456)
{
	return [$foo, $bar];
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ContainerTest extends TestCase
{
	/**
	 *
	 */
	public function testBasic()
	{
		$container = new Container;

		$foo = $container->factory('mako\tests\unit\syringe\Foo');

		$this->assertInstanceOf('\StdClass', $foo->stdClass);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Unable to resolve the [ $bax ] parameter of [ mako\tests\unit\syringe\Fox::__construct ].
	 */
	public function testClassInstantiationWithUnresolvableParameters()
	{
		$container = new Container;

		$foo = $container->factory('mako\tests\unit\syringe\Fox');
	}

	/**
	 *
	 */
	public function testParametersFromReflection()
	{
		$container = new Container;

		$bar = $container->factory('mako\tests\unit\syringe\Bar');

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(456, $bar->bar);
	}

	/**
	 *
	 */
	public function testNumericParameters()
	{
		$container = new Container;

		$bar = $container->factory('mako\tests\unit\syringe\Bar', ['abc', 'def']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);

		//

		$container = new Container;

		$bar = $container->factory('mako\tests\unit\syringe\Bar', [1 => 'def', 0 => 'abc']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);
	}

	/**
	 *
	 */
	public function testAssociativeParameters()
	{
		$container = new Container;

		$bar = $container->factory('mako\tests\unit\syringe\Bar', ['bar' => 789]);

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(789, $bar->bar);
	}

	/**
	 *
	 */
	public function testMixedParameters()
	{
		$container = new Container;

		$bar = $container->factory('mako\tests\unit\syringe\Bar', ['bar' => 'def', 0 => 'abc']);

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
	 * @expectedExceptionMessage Unable to create a [ mako\tests\unit\syringe\StoreInterface ] instance.
	 */
	public function testInterfaceInstantiation()
	{
		$container = new Container;

		$baz = $container->factory('mako\tests\unit\syringe\StoreInterface');
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
	 * @expectedExceptionMessage The factory closure must return an object.
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
	 *
	 */
	public function testCallFunction()
	{
		$container = new Container;

		$this->assertEquals([123, 456], $container->call('\mako\tests\unit\syringe\syringeFunction'));

		//

		$container = new Container;

		$this->assertEquals([456, 456], $container->call('\mako\tests\unit\syringe\syringeFunction', [456]));

		//

		$container = new Container;

		$this->assertEquals([456, 123], $container->call('\mako\tests\unit\syringe\syringeFunction', ['foo' => 456, 'bar' => 123]));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage Unable to resolve the [ $foo ] parameter of
	 *
	 * The entire exception message isn't included in the test because of some HHVM incompatibility that causes the test to fail
	 */
	public function testCallMethodWithUnresolvableParameters()
	{
		$container = new Container;

		$container->call(function($foo){});
	}

	/**
	 *
	 */
	public function testContextualDependencies()
	{
		$container = new Container;

		$container->registerContextualDependency(ContextClassA::class, ContextualInterface::class, ContextualImplementationA::class);
		$container->registerContextualDependency(ContextClassB::class, ContextualInterface::class, ContextualImplementationB::class);

		$a = $container->factory(ContextClassA::class);
		$b = $container->factory(ContextClassB::class);

		$this->assertInstanceOf(ContextualImplementationA::class, $a->implementation);
		$this->assertInstanceOf(ContextualImplementationB::class, $b->implementation);
	}

	/**
	 *
	 */
	public function testIsSingletonWithRegisteredInstance()
	{
		$container = new Container;

		$this->assertFalse($container->isSingleton('foo'));

		$this->assertFalse($container->isSingleton(stdClass::class));

		$container->registerInstance([stdClass::class, 'foo'], new stdClass);

		$this->assertTrue($container->isSingleton('foo'));

		$this->assertTrue($container->isSingleton(stdClass::class));
	}

	/**
	 *
	 */
	public function testIsSingletonWithFactory()
	{
		$container = new Container;

		$this->assertFalse($container->isSingleton('foo'));

		$this->assertFalse($container->isSingleton(stdClass::class));

		$container->registerSingleton([stdClass::class, 'foo'], function($container)
		{
			return new stdClass;
		});

		$this->assertTrue($container->isSingleton('foo'));

		$this->assertTrue($container->isSingleton(stdClass::class));
	}

	/**
	 *
	 */
	public function testReplaceRegisteredWithClosure()
	{
		$container = new Container;

		$container->register(ReplaceA::class, function($container)
		{
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function($replaceA)
			{
				$this->replaceA = $replaceA;
			})->bindTo($replaceB, ReplaceB::class));

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replace(ReplaceA::class, function($container)
		{
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredWithSetter()
	{
		$container = new Container;

		$container->register(ReplaceA::class, function($container)
		{
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, [$replaceB, 'setReplaceA']);

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replace(ReplaceA::class, function($container)
		{
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredSingletonWithClosure()
	{
		$container = new Container;

		$container->registerSingleton(ReplaceA::class, function($container)
		{
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function($replaceA)
			{
				$this->replaceA = $replaceA;
			})->bindTo($replaceB, ReplaceB::class));

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceSingleton(ReplaceA::class, function($container)
		{
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegistereSingletondWithSetter()
	{
		$container = new Container;

		$container->registerSingleton(ReplaceA::class, function($container)
		{
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, [$replaceB, 'setReplaceA']);

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceSingleton(ReplaceA::class, function($container)
		{
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredInstanceWithClosure()
	{
		$container = new Container;

		$container->registerInstance(ReplaceA::class, new ReplaceA('original'));

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function($replaceA)
			{
				$this->replaceA = $replaceA;
			})->bindTo($replaceB, ReplaceB::class));

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceInstance(ReplaceA::class, new ReplaceA('replacement'));

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisterInstanceWithSetter()
	{
		$container = new Container;

		$container->registerInstance(ReplaceA::class, new ReplaceA('original'));

		$container->register(ReplaceB::class, function($container)
		{
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, [$replaceB, 'setReplaceA']);

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceInstance(ReplaceA::class, new ReplaceA('replacement'));

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Unable to replace [ mako\tests\unit\syringe\ReplaceA ] as it hasn't been registered.
	 */
	public function testReplaceUnregistered()
	{
		$container = new Container;

		$container->replace(ReplaceA::class, function($container)
		{
			return new ReplaceA('replacement');
		});
	}

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Unable to replace [ mako\tests\unit\syringe\ReplaceA ] as it hasn't been registered.
	 */
	public function testReplaceUnregisteredInstance()
	{
		$container = new Container;

		$container->replaceInstance(ReplaceA::class, new ReplaceA('replacement'));
	}
}
