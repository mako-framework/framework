<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\onion;

use PHPUnit_Framework_TestCase;

use mako\onion\Onion;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Foo
{
	public function handle()
	{
		return 'foo';
	}
}

class FooMiddleware1
{
	public function execute($next)
	{
		return 'MW1B' . $next() . 'MW1A';
	}
}

class FooMiddleware2
{
	public function execute($next)
	{
		return 'MW2B' . $next() . 'MW2A';
	}
}

class Bar
{
	public function handle($bar)
	{
		return $bar;
	}
}

class BarMiddleware1
{
	public function execute($bar, $next)
	{
		return 'MW1B' . $next($bar) . 'MW1A';
	}
}

class BarMiddleware2
{
	public function execute($bar, $next)
	{
		return 'MW2B' . $next($bar) . 'MW2A';
	}
}

class BazMiddleware1
{
	protected $separator;

	public function __construct($separator)
	{
		$this->separator = $separator;
	}

	public function execute($next)
	{
		return str_replace(' ', $this->separator, $next());
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class OnionTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function testMiddleware()
	{
		$onion = new Onion;

		$onion->addLayer(FooMiddleware1::class);
		$onion->addLayer(FooMiddleware2::class);

		$result = $onion->peel(new Foo);

		$this->assertSame('MW1BMW2BfooMW2AMW1A', $result);

		//

		$onion = new Onion;

		$onion->addLayer(FooMiddleware1::class, [], false);
		$onion->addLayer(FooMiddleware2::class, [], false);

		$result = $onion->peel(new Foo);

		$this->assertSame('MW2BMW1BfooMW1AMW2A', $result);
	}

	/**
	 *
	 */
	public function testMiddlewareWithParams()
	{
		$onion = new Onion;

		$onion->addLayer(BarMiddleware1::class);
		$onion->addLayer(BarMiddleware2::class);

		$result = $onion->peel(new Bar, ['bar']);

		$this->assertSame('MW1BMW2BbarMW2AMW1A', $result);
	}

	/**
	 *
	 */
	public function testMiddlewareWithClosureAndParams()
	{
		$onion = new Onion;

		$onion->addLayer(BarMiddleware1::class);
		$onion->addLayer(BarMiddleware2::class);

		$result = $onion->peel(function($baz)
		{
			return $baz;
		}, ['baz']);

		$this->assertSame('MW1BMW2BbazMW2AMW1A', $result);
	}

	/**
	 *
	 */
	public function testAddInnerLayer()
	{
		$onion = new Onion;

		$onion->addInnerLayer(FooMiddleware1::class);
		$onion->addInnerLayer(FooMiddleware2::class);

		$result = $onion->peel(new Foo);

		$this->assertSame('MW1BMW2BfooMW2AMW1A', $result);

	}

	/**
	 *
	 */
	public function testAddOuterLayer()
	{
		$onion = new Onion;

		$onion->addOuterLayer(FooMiddleware1::class);
		$onion->addOuterLayer(FooMiddleware2::class);

		$result = $onion->peel(new Foo);

		$this->assertSame('MW2BMW1BfooMW1AMW2A', $result);

	}

	/**
	 *
	 */
	public function testMiddlewareWithConstructorParameters()
	{
		$onion = new Onion;

		$onion->addLayer(BazMiddleware1::class, ['separator' => '_']);

		$result = $onion->peel(function()
		{
			return 'hello, world!';
		});

		$this->assertSame('hello,_world!', $result);
	}

	/**
	 *
	 */
	public function testMiddlewareWithConstructorParametersAtRuntime()
	{
		$onion = new Onion;

		$onion->addLayer(BazMiddleware1::class);

		$result = $onion->peel(function()
		{
			return 'hello, world!';
		}, [], [BazMiddleware1::class => ['separator' => '_']]);

		$this->assertSame('hello,_world!', $result);
	}
}