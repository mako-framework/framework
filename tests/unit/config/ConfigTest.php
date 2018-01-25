<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\config;

use Mockery;

use mako\config\Config;
use mako\tests\TestCase;

/**
 * @group unit
 */
class ConfigTest extends TestCase
{
	/**
	 *
	 */
	public function getLoader()
	{
		return Mockery::mock('mako\config\loaders\LoaderInterface');
	}

	/**
	 *
	 */
	public function testBasic()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->getLoadedConfiguration());
	}

	/**
	 *
	 */
	public function testBasicWithEnvironment()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', 'foo')->andReturn(['greeting' => 'hello']);

		$config = new Config($loader, 'foo');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->getLoadedConfiguration());
	}

	/**
	 *
	 */
	public function testSet()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn([]);

		$config = new Config($loader);

		$this->assertNull($config->get('settings.greeting'));

		$config->set('settings.greeting', 'hello');

		$this->assertEquals('hello', $config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('load')->once()->with('settings', null)->andReturn(['greeting' => 'hello']);

		$config = new Config($loader);

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$config->remove('settings.greeting');

		$this->assertNull($config->get('settings.greeting'));
	}

	/**
	 *
	 */
	public function testGetLoader()
	{
		$loader = $this->getLoader();

		$config = new Config($loader);

		$this->assertInstanceOf('mako\config\loaders\LoaderInterface', $config->getLoader());
	}
}
