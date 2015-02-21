<?php

namespace mako\tests\unit\common;

use PHPUnit_Framework_TestCase;

use mako\common\ConfigurableTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Configurable
{
	use ConfigurableTrait;

	public function getConfiguration($name)
	{
		return isset($this->configurations[$name]) ? $this->configurations[$name] : null;
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ConfigurableTraitTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testConstructor()
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$this->assertSame(['foo_config'], $configurable->getConfiguration('foo'));
	}

	/**
	 *
	 */

	public function testAddConfiguration()
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$configurable->addConfiguration('bar', ['bar_config']);

		$this->assertSame(['bar_config'], $configurable->getConfiguration('bar'));
	}

	/**
	 *
	 */

	public function testRemoveConfiguration()
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$configurable->addConfiguration('bar', ['bar_config']);

		$this->assertSame(['bar_config'], $configurable->getConfiguration('bar'));

		$configurable->removeConfiguration('bar');

		$this->assertSame(null, $configurable->getConfiguration('bar'));
	}
}