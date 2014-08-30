<?php

namespace mako\tests\unit\i18n;

use \mako\i18n\Loader;

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

	protected function loadStrings($fileSystem)
	{
		$fileSystem->shouldReceive('glob')->once()->with('/app/i18n/en_US/strings/*.php', GLOB_NOSORT)->andReturn
		(
			[
				'/app/i18n/en_US/strings/foo.php', 
				'/app/i18n/en_US/strings/bar.php',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/strings/foo.php')->andReturn
		(
			[
				'foo' => 'foobar',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/strings/bar.php')->andReturn
		(
			[
				'bar' => 'barfoo',
			]
		);

		return $fileSystem;
	}

	/**
	 * 
	 */

	protected function loadPackages($fileSystem)
	{
		$fileSystem->shouldReceive('glob')->once()->with('/app/packages/foo/i18n/en_US/strings/*.php', GLOB_NOSORT)->andReturn
		(
			[
				'/app/packages/foo/i18n/en_US/strings/baz.php',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/foo/i18n/en_US/strings/baz.php')->andReturn
		(
			[
				'baz' => 'bazfoo',
			]
		);

		return $fileSystem;
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
		$loader = new Loader($this->loadStrings($this->getFileSystem()), '/app/i18n');

		$strings = $loader->loadStrings('en_US');

		$this->assertEquals(['foo' => ['foo' => 'foobar'], 'bar' => ['bar' => 'barfoo']], $strings);
	}

	/**
	 * 
	 */

	public function testStringLoadingWithPackages()
	{
		$loader = new Loader($this->loadPackages($this->loadStrings($this->getFileSystem())), '/app/i18n');

		$loader->registerNamespace('foo', '/app/packages/foo/i18n');

		$strings = $loader->loadStrings('en_US');

		$this->assertEquals(['foo' => ['foo' => 'foobar'], 'bar' => ['bar' => 'barfoo'], 'foo::baz' => ['baz' => 'bazfoo']], $strings);
	}

	/**
	 * 
	 */

	public function testLoadInflection()
	{
		$loader = new Loader($this->loadInflection($this->getFileSystem()), '/app/i18n');

		$this->assertEquals('inflection', $loader->loadInflection('en_US'));
	}
}