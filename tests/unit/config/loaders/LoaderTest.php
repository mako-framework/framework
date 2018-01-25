<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\config\loaders;

use Mockery;

use mako\config\loaders\Loader;
use mako\tests\TestCase;
/**
 * @group unit
 */
class ConfigTest extends TestCase
{
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
	public function testLoad()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('settings'));
	}

	/**
	 * @expectedException \RuntimeException
	 */
	public function testLoadNonExistingFile()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->load('settings');
	}

	/**
	 *
	 */
	public function testLoadPackage()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings'));
	}

	/**
	 *
	 */
	public function testLoadPackageOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/packages/baz/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings'));
	}

	/**
	 *
	 */
	public function testLoadEvironmentOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

		$fileSystem->shouldReceive('has')->once()->with('/app/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

		$loader = new Loader($fileSystem, '/app/config');

		$this->assertEquals(['greeting' => 'konnichiwa', 'goodbye' => 'sayonara'], $loader->load('settings', 'dev'));
	}
}
