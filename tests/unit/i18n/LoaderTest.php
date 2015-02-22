<?php

namespace mako\tests\unit\i18n;

use mako\i18n\Loader;

use \Mockery as m;

/**
 * @group unit
 */

class LoaderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function tearDown()
	{
		m::close();
	}

	/**
	 *
	 */

	public function getFileSystem()
	{
		return m::mock('mako\file\FileSystem');
	}

	/**
	 *
	 */

	protected function loadInflection($fileSystem)
	{
		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/inflection.php')->andReturn('inflection');

		return $fileSystem;
	}

	/**
	 *
	 */

	public function testBasicStringLoading()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$strings = $loader->loadStrings('en_US', 'foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\i18n\Loader::loadStrings(): The [ /app/i18n/en_US/strings/foobar.php ] language file does not exist in the [ en_US ] language pack.
	 */

	public function testBasicNonExistingStringLoading()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/strings/foobar.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->loadStrings('en_US', 'foobar');
	}

	/**
	 *
	 */

	public function testStringLoadingWithPackages()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(false);

		$fileSystem->shouldReceive('exists')->once()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->registerNamespace('foo', '/app/packages/foo/i18n');

		$strings = $loader->loadStrings('en_US', 'foo::foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 *
	 */

	public function testStringLoadingWithPackagesOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('exists')->never()->with('/app/packages/foo/i18n/en_US/strings/foobar.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/packages/foo/en_US/strings/foobar.php')->andReturn(['foo' => 'bar']);

		$loader = new Loader($fileSystem, '/app/i18n');

		$loader->registerNamespace('foo', '/app/packages/foo/i18n');

		$strings = $loader->loadStrings('en_US', 'foo::foobar');

		$this->assertEquals(['foo' => 'bar'], $strings);
	}

	/**
	 *
	 */

	public function testLoadInflection()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/inflection.php')->andReturn('inflection');

		$loader = new Loader($fileSystem, '/app/i18n');

		$this->assertEquals('inflection', $loader->loadInflection('en_US'));
	}

	/**
	 *
	 */

	public function testLoadNonExistingInflection()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(false);

		$loader = new Loader($fileSystem, '/app/i18n');

		$this->assertEquals(null, $loader->loadInflection('en_US'));
	}
}