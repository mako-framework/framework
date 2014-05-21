<?php

namespace mako\tests\unit\syringe;

use \mako\syringe\Container;

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

		$bar = $container->get('mako\tests\unit\syringe\Bar');

		$this->assertEquals(123, $bar->foo);
		$this->assertEquals(456, $bar->bar);
	}
}