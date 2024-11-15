<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\config\loaders;

use mako\config\loaders\exceptions\LoaderException;
use mako\config\loaders\Loader;
use mako\file\FileSystem;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class LoaderTest extends TestCase
{
	/**
	 * @return FileSystem|Mockery\MockInterface
	 */
	public function getFileSystem()
	{
		return Mockery::mock(FileSystem::class);
	}

	/**
	 *
	 */
	public function testLoad(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('settings'));
	}

	/**
	 *
	 */
	public function testLoadNonExistingFile(): void
	{
		$this->expectException(LoaderException::class);

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/settings.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->load('settings');
	}

	/**
	 *
	 */
	public function testLoadPackage(): void
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
	public function testLoadPackageOverride(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/packages/baz/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings'));
	}

	/**
	 *
	 */
	public function testLoadEvironmentOverride(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('settings', 'dev'));
	}

	/**
	 *
	 */
	public function testLoadEvironmentOverrideWithPackage(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/dev/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/dev/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

		$loader = new Loader($fileSystem, '/app/config');

		$loader->registerNamespace('baz', '/app/packages/baz/config');

		$this->assertEquals(['greeting' => 'hello'], $loader->load('baz::settings', 'dev'));
	}
}
