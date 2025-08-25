<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use mako\cache\stores\File;
use mako\file\FileSystem;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

#[Group('unit')]
class FileTest extends TestCase
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
	public function getSplFileObject(): MockInterface&stdClass
	{
		return Mockery::mock(stdClass::class); // ... because SplFileObject can't be mocked
	}

	/**
	 *
	 */
	public function testPut(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('put')->once()->with('/cache/foo.php', (31556926 + time()) . "\n" . serialize(123), true);

		$file = new File($fileSystem, '/cache');

		$file->put('foo', 123);

		//

		$fileSystem->shouldReceive('put')->once()->with('/cache/foo.php', (3600 + time()) . "\n" . serialize(123), true);

		$file = new File($fileSystem, '/cache');

		$file->put('foo', 123, 3600);
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(true);

		$splFileObject = $this->getSplFileObject();

		$splFileObject->shouldReceive('fgets')->once()->andReturn(2000000000);

		$fileSystem->shouldReceive('file')->once()->with('/cache/foo.php', 'r')->andReturn($splFileObject);

		$file = new File($fileSystem, '/cache');

		$this->assertTrue($file->has('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(true);

		$splFileObject = $this->getSplFileObject();

		$splFileObject->shouldReceive('fgets')->once()->andReturn(1984);

		$fileSystem->shouldReceive('file')->once()->with('/cache/foo.php', 'r')->andReturn($splFileObject);

		$file = new File($fileSystem, '/cache');

		$this->assertFalse($file->has('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(false);

		$file = new File($fileSystem, '/cache');

		$this->assertFalse($file->has('foo'));
	}

	/**
	 *
	 */
	public function testGet(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(true);

		$splFileObject = $this->getSplFileObject();

		$splFileObject->shouldReceive('fgets')->twice()->andReturn(2000000000, serialize('bar'));

		$splFileObject->shouldReceive('eof')->twice()->andReturn(false, true);

		$fileSystem->shouldReceive('file')->once()->with('/cache/foo.php', 'r')->andReturn($splFileObject);

		$file = new File($fileSystem, '/cache');

		$this->assertEquals('bar', $file->get('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(true);

		$splFileObject = $this->getSplFileObject();

		$splFileObject->shouldReceive('fgets')->once()->andReturn(1983);

		$fileSystem->shouldReceive('file')->once()->with('/cache/foo.php', 'r')->andReturn($splFileObject);

		$fileSystem->shouldReceive('remove')->once()->with('/cache/foo.php');

		$file = new File($fileSystem, '/cache');

		$this->assertNull($file->get('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(false);

		$file = new File($fileSystem, '/cache');

		$this->assertNull($file->get('foo'));
	}

	/**
	 *
	 */
	public function testRemove(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(true);

		$fileSystem->shouldReceive('remove')->once()->with('/cache/foo.php')->andReturn(true);

		$file = new File($fileSystem, '/cache');

		$this->assertTrue($file->remove('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(false);

		$file = new File($fileSystem, '/cache');

		$this->assertFalse($file->remove('foo'));
	}

	/**
	 *
	 */
	public function testClear(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('glob')->once()->with('/cache/*')->andReturn(['/cache/foo.php', '/cache/bar.php']);

		$fileSystem->shouldReceive('isFile')->once()->with('/cache/foo.php')->andReturn(true);

		$fileSystem->shouldReceive('isFile')->once()->with('/cache/bar.php')->andReturn(true);

		$fileSystem->shouldReceive('remove')->once()->with('/cache/foo.php')->andReturn(true);

		$fileSystem->shouldReceive('remove')->once()->with('/cache/bar.php')->andReturn(true);

		$file = new File($fileSystem, '/cache');

		$this->assertTrue($file->clear());

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('glob')->once()->with('/cache/*')->andReturn([]);

		$file = new File($fileSystem, '/cache');

		$this->assertTrue($file->clear());
	}
}
