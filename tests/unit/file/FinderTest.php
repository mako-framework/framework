<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\file;

use mako\file\FileInfo;
use mako\file\Finder;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FinderTest extends TestCase
{
	/**
	 *
	 */
	public function testFinder(): void
	{
		$expectedFiles =
		[
			__DIR__ . '/files/one.txt'         => __DIR__ . '/files/one.txt',
			__DIR__ . '/files/two.php'         => __DIR__ . '/files/two.php',
			__DIR__ . '/files/files/three.txt' => __DIR__ . '/files/files/three.txt',
			__DIR__ . '/files/files/four.php'  => __DIR__ . '/files/files/four.php',
		];

		$finder = new Finder([__DIR__ . '/files']);

		$files = iterator_to_array($finder->find());

		sort($expectedFiles);

		sort($files);

		$this->assertNull($finder->getPattern());

		$this->assertNull($finder->getMaxDepth());

		$this->assertSame($expectedFiles, $files);
	}

	/**
	 *
	 */
	public function testFinderWithMaxDepth(): void
	{
		$expectedFiles =
		[
			__DIR__ . '/files/one.txt'         => __DIR__ . '/files/one.txt',
			__DIR__ . '/files/two.php'         => __DIR__ . '/files/two.php',
		];

		$finder = new Finder([__DIR__ . '/files']);

		$files = iterator_to_array($finder->setMaxDepth(0)->find());

		sort($expectedFiles);

		sort($files);

		$this->assertNull($finder->getPattern());

		$this->assertSame(0, $finder->getMaxDepth());

		$this->assertSame($expectedFiles, $files);
	}

	/**
	 *
	 */
	public function testFinderWithPattern(): void
	{
		$expectedFiles =
		[
			__DIR__ . '/files/two.php'         => __DIR__ . '/files/two.php',
			__DIR__ . '/files/files/four.php'  => __DIR__ . '/files/files/four.php',
		];

		$finder = new Finder([__DIR__ . '/files']);

		$files = iterator_to_array($finder->setPattern('/\.php$/')->find());

		sort($expectedFiles);

		sort($files);

		$this->assertSame('/\.php$/', $finder->getPattern());

		$this->assertNull($finder->getMaxDepth());

		$this->assertSame($expectedFiles, $files);
	}

	/**
	 *
	 */
	public function testFindAs(): void
	{
		$finder = new Finder([__DIR__ . '/files']);

		$files = iterator_to_array($finder->setMaxDepth(0)->setPattern('/\.txt$/')->findAs(FileInfo::class));

		$this->assertInstanceOf(FileInfo::class, current($files));
	}
}
