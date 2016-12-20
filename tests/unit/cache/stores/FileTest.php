<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cache\stores;

use Mockery;
use PHPUnit_Framework_TestCase;

use mako\cache\stores\File;

/**
 * @group unit
 */
class FileTest extends PHPUnit_Framework_TestCase
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
	public function getSplFileObject()
	{
		return Mockery::mock('StdClass'); // ... because SplFileObject can't be mocked
	}

	/**
	 *
	 */
	public function testPut()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('put')->once()->with('/cache/foo.php', (31556926 + time()) . "\n" . serialize(123), LOCK_EX);

		$file = new File($fileSystem, '/cache');

		$file->put('foo', 123);

		//

		$fileSystem->shouldReceive('put')->once()->with('/cache/foo.php', (3600 + time()) . "\n" . serialize(123), LOCK_EX);

		$file = new File($fileSystem, '/cache');

		$file->put('foo', 123, 3600);
	}

	/**
	 *
	 */
	public function testHas()
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
	public function testGet()
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

		$this->assertFalse($file->get('foo'));

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/cache/foo.php')->andReturn(false);

		$file = new File($fileSystem, '/cache');

		$this->assertFalse($file->get('foo'));
	}

	/**
	 *
	 */
	public function testRemove()
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
	public function testClear()
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
