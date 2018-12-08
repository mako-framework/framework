<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common;

use mako\common\traits\ConfigurableTrait;
use mako\tests\TestCase;

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
class ConfigurableTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testConstructor(): void
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$this->assertSame(['foo_config'], $configurable->getConfiguration('foo'));
	}

	/**
	 *
	 */
	public function testAddConfiguration(): void
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$configurable->addConfiguration('bar', ['bar_config']);

		$this->assertSame(['bar_config'], $configurable->getConfiguration('bar'));
	}

	/**
	 *
	 */
	public function testRemoveConfiguration(): void
	{
		$configurable = new Configurable('foo', ['foo' => ['foo_config']]);

		$configurable->addConfiguration('bar', ['bar_config']);

		$this->assertSame(['bar_config'], $configurable->getConfiguration('bar'));

		$configurable->removeConfiguration('bar');

		$this->assertSame(null, $configurable->getConfiguration('bar'));
	}
}
