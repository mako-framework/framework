<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\syringe;

use mako\syringe\attributes\InjectorInterface;
use mako\syringe\Container;
use mako\syringe\exceptions\ContainerException;
use mako\syringe\exceptions\UnableToInstantiateException;
use mako\syringe\exceptions\UnableToResolveParameterException;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

use function mako\syringe\intersection;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Foo
{
	public function __construct(
		public stdClass $stdClass
	) {
	}
}

class Bar
{
	public function __construct(
		public $foo = 123,
		public $bar = 456
	) {
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
	public function __construct(
		public StoreInterface $store
	) {
	}
}

class Baq
{
	public $baq;

	public function setBaq($baq = 123): void
	{
		$this->baq = $baq;
	}
}

class Fox
{
	public function __construct(
		public $bax
	) {
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
	public function __construct(
		public ContextualInterface $implementation
	) {
	}
}

class ContextClassB
{
	public function __construct(
		public ContextualInterface $implementation
	) {
	}
}

class ContextualMethods
{
	public function foo(ContextualInterface $implementation): ContextualInterface
	{
		return $implementation;
	}

	public function bar(ContextualInterface $implementation): ContextualInterface
	{
		return $implementation;
	}
}

class ReplaceA
{
	public function __construct(
		protected $value
	) {
	}

	public function getValue()
	{
		return $this->value;
	}
}

class ReplaceB
{
	public function __construct(
		protected ReplaceA $replaceA
	) {
	}

	public function setReplaceA(ReplaceA $replaceA): void
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

class ImpossibleToResolveDependencyA
{
	public function __construct(
		public ?StoreInterface $store = null
	) {
	}
}

class ImpossibleToResolveDependencyB
{
	public function __construct(
		public ?StoreInterface $store = null
	) {
	}
}

class ImpossibleToResolveDependencyC
{
	public function __construct(
		public ?StoreInterface $store
	) {
	}
}

interface IA
{

}

interface IB
{

}

class AB implements IA, IB
{

}

class Intersection
{
	public function __construct(
		public IA&IB $ab
	) {
	}
}

class NullableIntersection
{
	public function __construct(
		public null|(IA&IB) $ab
	) {
	}
}

class InjectString implements InjectorInterface
{
	public function __construct(
		protected string $string = 'foobar'
	) {
	}

    public function getParameterValue(): string
    {
		return $this->string;
	}

}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

#[Group('unit')]
class ContainerTest extends TestCase
{
	/**
	 *
	 */
	public function testBasic(): void
	{
		$container = new Container;

		$foo = $container->factory(Foo::class);

		$this->assertInstanceOf(stdClass::class, $foo->stdClass);
	}

	/**
	 *
	 */
	public function testClassInstantiationWithUnresolvableParameters(): void
	{
		$this->expectException(UnableToResolveParameterException::class);

		$this->expectExceptionMessage('Unable to resolve the [ $bax ] parameter of [ mako\tests\unit\syringe\Fox::__construct ].');

		$container = new Container;

		$foo = $container->factory(Fox::class);
	}

	/**
	 *
	 */
	public function testParametersFromReflection(): void
	{
		$container = new Container;

		$bar = $container->factory(Bar::class);

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(456, $bar->bar);
	}

	/**
	 *
	 */
	public function testNumericParameters(): void
	{
		$container = new Container;

		$bar = $container->factory(Bar::class, ['abc', 'def']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);

		//

		$container = new Container;

		$bar = $container->factory(Bar::class, [1 => 'def', 0 => 'abc']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);
	}

	/**
	 *
	 */
	public function testAssociativeParameters(): void
	{
		$container = new Container;

		$bar = $container->factory(Bar::class, ['bar' => 789]);

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(789, $bar->bar);
	}

	/**
	 *
	 */
	public function testMixedParameters(): void
	{
		$container = new Container;

		$bar = $container->factory(Bar::class, ['bar' => 'def', 0 => 'abc']);

		$this->assertEquals('abc', $bar->foo);
		$this->assertEquals('def', $bar->bar);
	}

	/**
	 *
	 */
	public function testImplementationInjection(): void
	{
		$container = new Container;

		$container->register(StoreInterface::class, Store::class);

		$baz = $container->get(Baz::class);

		$this->assertInstanceOf(Store::class, $baz->store);
	}

	/**
	 *
	 */
	public function testInterfaceInstantiation(): void
	{
		$this->expectException(UnableToInstantiateException::class);

		$this->expectExceptionMessage('Unable to create a [ mako\tests\unit\syringe\StoreInterface ] instance.');

		$container = new Container;

		$container->factory(StoreInterface::class);
	}

	/**
	 *
	 */
	public function testGetUsingAlias(): void
	{
		$container = new Container;

		$container->register([Foo::class, 'foo'], Foo::class);

		$foo = $container->get('foo');

		$this->assertInstanceOf(Foo::class, $foo);
	}

	/**
	 *
	 */
	public function testRegisterClosure(): void
	{
		$container = new Container;

		$container->register([Bar::class, 'bar'], function () {
			return new Bar('uvw', 'xyz');
		});

		$bar = $container->get('bar');

		$this->assertInstanceOf(Bar::class, $bar);

		$this->assertEquals('uvw', $bar->foo);
		$this->assertEquals('xyz', $bar->bar);
	}

	/**
	 *
	 */
	public function testRegisterInstance(): void
	{
		$container = new Container;

		$baq = new Baq;

		$baq->setBaq('foobar');

		$container->registerInstance([Baq::class, 'baq'], $baq);

		$baq = $container->get('baq');

		$this->assertInstanceOf(Baq::class, $baq);

		$this->assertSame('foobar', $baq->baq);
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$container = new Container;

		$container->register([Foo::class, 'foo'], Foo::class);

		$this->assertTrue($container->has(Foo::class));

		$this->assertTrue($container->has('foo'));

		$this->assertFalse($container->has(Bar::class));

		$this->assertFalse($container->has('bar'));
	}

	/**
	 *
	 */
	public function testHasInstanceOf(): void
	{
		$container = new Container;

		$container->registerSingleton([Bar::class, 'bar'], function () {
			return new Bar(bin2hex(random_bytes(16)), bin2hex(random_bytes(16)));
		});

		$this->assertFalse($container->hasInstanceOf(Bar::class));
		$this->assertFalse($container->hasInstanceOf('bar'));

		$container->get(Bar::class);

		$this->assertTrue($container->hasInstanceOf(Bar::class));
		$this->assertTrue($container->hasInstanceOf('bar'));
	}

	/**
	 *
	 */
	public function testRegisterSingleton(): void
	{
		$container = new Container;

		$container->registerSingleton([Bar::class, 'bar'], function () {
			return new Bar(bin2hex(random_bytes(16)), bin2hex(random_bytes(16)));
		});

		$this->assertEquals($container->get('bar'), $container->get('bar'));

		$this->assertNotEquals($container->get('bar'), $container->getFresh('bar'));
	}

	/**
	 *
	 */
	public function testCallClosure(): void
	{
		$closure = function (Bar $bar) {
			return $bar;
		};

		$container = new Container;

		$returnValue = $container->call($closure);

		$this->assertInstanceOf(Bar::class, $returnValue);

		//

		$closure = function (Bar $bar, $foo = 123) {
			return [$bar, $foo];
		};

		$container = new Container;

		$returnValue = $container->call($closure);

		$this->assertInstanceOf(Bar::class, $returnValue[0]);

		$this->assertSame(123, $returnValue[1]);

		//

		$closure = function (Bar $bar, $foo = 123) {
			return [$bar, $foo];
		};

		$container = new Container;

		$returnValue = $container->call($closure, ['foo' => 456]);

		$this->assertInstanceOf(Bar::class, $returnValue[0]);

		$this->assertSame(456, $returnValue[1]);
	}

	/**
	 *
	 */
	public function testCallMethod(): void
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
	public function testCallFunction(): void
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
	 * The entire exception message isn't included in the test because of some HHVM incompatibility that causes the test to fail.
	 */
	public function testCallMethodWithUnresolvableParameters(): void
	{
		$this->expectException(UnableToResolveParameterException::class);

		$this->expectExceptionMessage('Unable to resolve the [ $foo ] parameter of');

		$container = new Container;

		$container->call(function ($foo): void {});
	}

	/**
	 *
	 */
	public function testCallOnInvokableObject(): void
	{
		$object = new class {
			public function __invoke()
			{
				return 'foobar';
			}
		};

		$container = new Container;

		$this->assertSame('foobar', $container->call($object));
	}

	/**
	 *
	 */
	public function testContextualDependencies(): void
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
	public function testContextualDependencyOnAClassMethod(): void
	{
		$container = new Container;

		$container->registerContextualDependency([ContextualMethods::class, 'foo'], ContextualInterface::class, ContextualImplementationA::class);
		$container->registerContextualDependency([ContextualMethods::class, 'bar'], ContextualInterface::class, ContextualImplementationB::class);

		$class = new ContextualMethods;

		$this->assertInstanceOf(ContextualImplementationA::class, $container->call([$class, 'foo']));
		$this->assertInstanceOf(ContextualImplementationB::class, $container->call([$class, 'bar']));
	}

	/**
	 *
	 */
	public function testIsSingletonWithRegisteredInstance(): void
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
	public function testIsSingletonWithFactory(): void
	{
		$container = new Container;

		$this->assertFalse($container->isSingleton('foo'));

		$this->assertFalse($container->isSingleton(stdClass::class));

		$container->registerSingleton([stdClass::class, 'foo'], function ($container) {
			return new stdClass;
		});

		$this->assertTrue($container->isSingleton('foo'));

		$this->assertTrue($container->isSingleton(stdClass::class));
	}

	/**
	 *
	 */
	public function testReplaceRegisteredWithClosure(): void
	{
		$container = new Container;

		$container->register(ReplaceA::class, function ($container) {
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function ($container) {
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function ($replaceA): void {
				$this->replaceA = $replaceA;
			})->bindTo($replaceB, ReplaceB::class));

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replace(ReplaceA::class, function ($container) {
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredWithSetter(): void
	{
		$container = new Container;

		$container->register(ReplaceA::class, function ($container) {
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function ($container) {
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, [$replaceB, 'setReplaceA']);

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replace(ReplaceA::class, function ($container) {
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredSingletonWithClosure(): void
	{
		$container = new Container;

		$container->registerSingleton(ReplaceA::class, function ($container) {
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function ($container) {
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function ($replaceA): void {
				$this->replaceA = $replaceA;
			})->bindTo($replaceB, ReplaceB::class));

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceSingleton(ReplaceA::class, function ($container) {
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegistereSingletondWithSetter(): void
	{
		$container = new Container;

		$container->registerSingleton(ReplaceA::class, function ($container) {
			return new ReplaceA('original');
		});

		$container->register(ReplaceB::class, function ($container) {
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, [$replaceB, 'setReplaceA']);

			return $replaceB;
		});

		$replaceB = $container->get(ReplaceB::class);

		$this->assertSame('original', $replaceB->getReplaceAValue());

		$container->replaceSingleton(ReplaceA::class, function ($container) {
			return new ReplaceA('replacement');
		});

		$this->assertSame('replacement', $replaceB->getReplaceAValue());
	}

	/**
	 *
	 */
	public function testReplaceRegisteredInstanceWithClosure(): void
	{
		$container = new Container;

		$container->registerInstance(ReplaceA::class, new ReplaceA('original'));

		$container->register(ReplaceB::class, function ($container) {
			$replaceB = new ReplaceB($container->get(ReplaceA::class));

			$container->onReplace(ReplaceA::class, (function ($replaceA): void {
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
	public function testReplaceRegisterInstanceWithSetter(): void
	{
		$container = new Container;

		$container->registerInstance(ReplaceA::class, new ReplaceA('original'));

		$container->register(ReplaceB::class, function ($container) {
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
	 *
	 */
	public function testReplaceUnregistered(): void
	{
		$this->expectException(ContainerException::class);

		$this->expectExceptionMessage('Unable to replace [ mako\tests\unit\syringe\ReplaceA ] as it hasn\'t been registered.');

		$container = new Container;

		$container->replace(ReplaceA::class, function ($container) {
			return new ReplaceA('replacement');
		});
	}

	/**
	 *
	 */
	public function testReplaceUnregisteredInstance(): void
	{
		$this->expectException(ContainerException::class);

		$this->expectExceptionMessage('Unable to replace [ mako\tests\unit\syringe\ReplaceA ] as it hasn\'t been registered.');

		$container = new Container;

		$container->replaceInstance(ReplaceA::class, new ReplaceA('replacement'));
	}

	/**
	 *
	 */
	public function testImpossibleToResolveDendenciesThatAreNullable(): void
	{
		$container = new Container;

		$this->assertInstanceOf(ImpossibleToResolveDependencyA::class, $object = $container->get(ImpossibleToResolveDependencyA::class));

		$this->assertNull($object->store);

		//

		$container = new Container;

		$this->assertInstanceOf(ImpossibleToResolveDependencyB::class, $object = $container->get(ImpossibleToResolveDependencyB::class));

		$this->assertNull($object->store);

		//

		$container = new Container;

		$this->assertInstanceOf(ImpossibleToResolveDependencyC::class, $object = $container->get(ImpossibleToResolveDependencyC::class));

		$this->assertNull($object->store);
	}

	/**
	 *
	 */
	public function testGetInstanceClassNames(): void
	{
		$container = new Container;

		$this->assertEmpty($container->getInstanceClassNames());

		$container->registerSingleton([stdClass::class, 'foo'], function ($container) {
			return new stdClass;
		});

		$this->assertEmpty($container->getInstanceClassNames());

		$container->get('foo');

		$this->assertSame([stdClass::class], $container->getInstanceClassNames());
	}

	/**
	 *
	 */
	public function testRemoveInstance(): void
	{
		$container = new Container;

		$container->registerSingleton([stdClass::class, 'foo'], function ($container) {
			return new stdClass;
		});

		$container->get('foo');

		$this->assertSame([stdClass::class], $container->getInstanceClassNames());

		$container->removeInstance(stdClass::class);

		$this->assertEmpty($container->getInstanceClassNames());
	}

	/**
	 *
	 */
	public function testResolveIntersectionType(): void
	{
		$container = new Container;

		$container->register(intersection(IA::class, IB::class), AB::class);

		$object = $container->get(Intersection::class);

		$this->assertInstanceOf(AB::class, $object->ab);
	}

	/**
	 *
	 */
	public function testResolveIntersectionTypeWithoutRegisteredHint(): void
	{
		$this->expectException(UnableToResolveParameterException::class);

		$container = new Container;

		$object = $container->get(Intersection::class);
	}

	/**
	 *
	 */
	public function testResolveNullableIntersectionType(): void
	{
		$container = new Container;

		$container->register(intersection(IA::class, IB::class), AB::class);

		$object = $container->get(NullableIntersection::class);

		$this->assertInstanceOf(AB::class, $object->ab);
	}

	/**
	 *
	 */
	public function testResolveNullableIntersectionTypeWithoutRegisteredHint(): void
	{
		$container = new Container;

		$object = $container->get(NullableIntersection::class);

		$this->assertNull($object->ab);
	}

	/**
	 *
	 */
	public function testInjectorAttribute(): void
	{
		$container = new Container;

		$returnValue = $container->call(static fn (#[InjectString] string $string) => $string);

		$this->assertSame('foobar', $returnValue);

		//

		$returnValue = $container->call(static fn (#[InjectString('barfoo')] string $string) => $string);

		$this->assertSame('barfoo', $returnValue);

		//

		$returnValue = $container->call(static fn (#[InjectString('barfoo')] string $string) => $string, ['string' => 'baz']);

		$this->assertSame('baz', $returnValue);
	}
}
