<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\tests\unit\config;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\config\Config;

/**
 * @group unit
 */
class ConfigTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */
	public function tearDown()
	{
		Mockery::close();
	}

	/**
	 *
	 */
	public function getFileSystem()
	{
		return Mockery::mock('mako\file\FileSystem');
	}

	/**
	 *
	 */
	public function testBasic()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app/config');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));

		$this->assertEquals(['settings' => ['greeting' => 'hello']], $config->getLoadedConfiguration());
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testBasicNonExistingFile()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(false);

		$config = new Config($fileSystem, '/app/config');

		$config->get('settings.greeting');
	}

	/**
	 *
	 */
	public function testPackage()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app/config');

		$config->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals('hello', $config->get('baz::settings.greeting'));
	}

	/**
	 *
	 */
	public function testPackageOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/packages/baz/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app/config');

		$config->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals('hello', $config->get('baz::settings.greeting'));
	}

	/**
	 *
	 */
	public function testEvironmentOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

		$fileSystem->shouldReceive('has')->once()->with('/app/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

		$config = new Config($fileSystem, '/app/config');

		$config->setEnvironment('dev');

		$this->assertEquals('konnichiwa', $config->get('settings.greeting'));

		$this->assertEquals('sayonara', $config->get('settings.goodbye'));
	}

	/**
	 *
	 */
	public function testPackageEvironmentOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/dev/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

		$config = new Config($fileSystem, '/app/config');

		$config->setEnvironment('dev');

		$config->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals('konnichiwa', $config->get('baz::settings.greeting'));

		$this->assertEquals('sayonara', $config->get('baz::settings.goodbye'));
	}

	/**
	 *
	 */
	public function testSet()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn([]);

		$config = new Config($fileSystem, '/app/config');

		$this->assertNull($config->get('settings.greeting'));

		$config->set('settings.greeting', 'hello');

		$this->assertEquals('hello', $config->get('settings.greeting'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\config\Config::load(): The [ settings ] config file does not exist.
	 */
	public function testSetWithNonExistingFile()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(false);

		$config = new Config($fileSystem, '/app/config');

		$config->set('settings.greeting', 'hello');
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app/config');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$config->remove('settings.greeting');

		$this->assertNull($config->get('settings.greeting'));
	}
}