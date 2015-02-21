<?php

namespace mako\tests\unit\common;

use PHPUnit_Framework_TestCase;

use mako\common\NamespacedFileLoaderTrait;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class NamespacedFileLoader
{
	use NamespacedFileLoaderTrait;

	public function getPath($file, $extension = null, $suffix = null)
	{
		return $this->getFilePath($file, $extension, $suffix);
	}

	public function getCascadingPath($file, $extension = null, $suffix = null)
	{
		return $this->getCascadingFilePaths($file, $extension, $suffix);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class NamespacedFileLoaderTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testGetPath()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame('/foo.php', $loader->getPath('foo'));

		$this->assertSame('/path/foo.php', $loader->getPath('ns::foo'));
	}

	/**
	 *
	 */

	public function testSetExtension()
	{
		$loader = new NamespacedFileLoader;

		$loader->setExtension('.txt');

		$this->assertSame('/foo.txt', $loader->getPath('foo'));
	}

	/**
	 *
	 */

	public function testSetPath()
	{
		$loader = new NamespacedFileLoader;

		$loader->setPath('/bar');

		$this->assertSame('/bar/foo.php', $loader->getPath('foo'));
	}

	/**
	 *
	 */

	public function testGetPathWithCustomExtension()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame('/foo.txt', $loader->getPath('foo', '.txt'));

		$this->assertSame('/path/foo.txt', $loader->getPath('ns::foo', '.txt'));
	}

	/**
	 *
	 */

	public function testGetPathWithSuffix()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame('/bar/foo.php', $loader->getPath('foo', null, 'bar'));

		$this->assertSame('/path/bar/foo.php', $loader->getPath('ns::foo', null, 'bar'));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testGetPathWithSuffixUnknownNamespace()
	{
		$loader = new NamespacedFileLoader;

		$this->assertSame('/foo.php', $loader->getPath('foo'));

		$this->assertSame('/path/foo.php', $loader->getPath('ns::foo'));
	}

	/**
	 *
	 */

	public function testGetCascadingPath()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame(['/foo.php'], $loader->getCascadingPath('foo'));

		$this->assertSame(['/packages/ns/foo.php', '/path/foo.php'], $loader->getCascadingPath('ns::foo'));
	}

	/**
	 *
	 */

	public function testGetCascadingPathWithCustomExtension()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame(['/foo.txt'], $loader->getCascadingPath('foo', '.txt'));

		$this->assertSame(['/packages/ns/foo.txt', '/path/foo.txt'], $loader->getCascadingPath('ns::foo', '.txt'));
	}

	/**
	 *
	 */

	public function testGetCascadingPathWithSuffix()
	{
		$loader = new NamespacedFileLoader;

		$loader->registerNamespace('ns', '/path');

		$this->assertSame(['/bar/foo.php'], $loader->getCascadingPath('foo', null, 'bar'));

		$this->assertSame(['/packages/ns/bar/foo.php', '/path/bar/foo.php'], $loader->getCascadingPath('ns::foo', null, 'bar'));
	}
}