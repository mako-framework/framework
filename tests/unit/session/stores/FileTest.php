<?php

namespace mako\tests\unit\session\stores;

use mako\session\stores\File;

use \Mockery as m;

/**
 * @group unit
 */

class FileTest extends \PHPUnit_Framework_TestCase
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

	public function testWrite()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions')->andReturn(true);

		$fileSystem->shouldReceive('putContents')->once()->with('/sessions/123', serialize('data'));

		$file = new File($fileSystem, '/sessions');

		$file->write('123', 'data', 123);
	}

	/**
	 *
	 */

	public function testRead()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('isReadable')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('getContents')->once()->with('/sessions/123')->andReturn(serialize('data'));

		$file = new File($fileSystem, '/sessions');

		$cached = $file->read('123');

		$this->assertEquals('data', $cached);

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/sessions/123')->andReturn(false);

		$file = new File($fileSystem, '/sessions');

		$cached = $file->read('123');

		$this->assertEquals([], $cached);
	}

	/**
	 *
	 */

	public function testDelete()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('delete')->once()->with('/sessions/123');

		$file = new File($fileSystem, '/sessions');

		$file->delete('123');
	}

	/**
	 *
	 */

	public function testGc()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('glob')->once()->with('/sessions/*')->andReturn(['/sessions/123', '/sessions/456']);

		$fileSystem->shouldReceive('lastModified')->once()->with('/sessions/123')->andReturn(2000000000);

		$fileSystem->shouldReceive('lastModified')->once()->with('/sessions/456')->andReturn(1983);

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions/456')->andReturn(true);

		$fileSystem->shouldReceive('delete')->once()->with('/sessions/456');

		$file = new File($fileSystem, '/sessions');

		$file->gc(1984);
	}
}