<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\i18n;

use mako\file\FileSystem;
use mako\i18n\loaders\exceptions\LoaderException;
use mako\i18n\loaders\Loader;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class LoaderTest extends TestCase
{
	/**
	 *
	 */
	public function getFileSystem(): FileSystem&MockInterface
	{
		return Mockery::mock(FileSystem::class);
	}

	/**
	 *
	 */
	protected function loadInflection($fileSystem)
	{
		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(['inflection']);

		return $fileSystem;
	}

	/**
	 *
	 */
	public function testBasicStringLoading(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$strings = $loader->loadStrings('en_US', 'foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 *
	 */
	public function testBasicNonExistingStringLoading(): void
	{
		$this->expectException(LoaderException::class);

		$this->expectExceptionMessage('The [ en_US ] language pack does not have a [ /app/i18n/en_US/strings/foobar.php ] file.');

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->loadStrings('en_US', 'foobar');
	}

	/**
	 *
	 */
	public function testStringLoadingWithPackages(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(false);

		$fileSystem->shouldReceive('has')->once()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->registerNamespace('foo', '/app/packages/foo/i18n');

		$strings = $loader->loadStrings('en_US', 'foo::foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 *
	 */
	public function testStringLoadingWithPackagesOverride(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('has')->never()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->registerNamespace('foo', '/app/packages/foo/i18n');

		$strings = $loader->loadStrings('en_US', 'foo::foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 *
	 */
	public function testLoadInflection(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(true);

		$fileSystem->shouldReceive('include')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(['inflection']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$this->assertEquals(['inflection'], $loader->loadInflection('en_US'));
	}

	/**
	 *
	 */
	public function testLoadNonExistingInflection(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/i18n');

		$this->assertEquals(null, $loader->loadInflection('en_US'));
	}
}
