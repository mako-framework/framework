<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\session\stores;

use mako\file\FileSystem;
use mako\session\stores\File;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

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
	public function testWrite(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions')->andReturn(true);

		$fileSystem->shouldReceive('put')->once()->with('/sessions/123', serialize(['data']));

		$file = new File($fileSystem, '/sessions');

		$file->write('123', ['data'], 123);
	}

	/**
	 *
	 */
	public function testRead(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('isReadable')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('get')->once()->with('/sessions/123')->andReturn(serialize(['data']));

		$file = new File($fileSystem, '/sessions');

		$cached = $file->read('123');

		$this->assertEquals(['data'], $cached);

		//

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/sessions/123')->andReturn(false);

		$file = new File($fileSystem, '/sessions');

		$cached = $file->read('123');

		$this->assertEquals([], $cached);
	}

	/**
	 *
	 */
	public function testDelete(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('has')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions/123')->andReturn(true);

		$fileSystem->shouldReceive('remove')->once()->with('/sessions/123');

		$file = new File($fileSystem, '/sessions');

		$file->delete('123');
	}

	/**
	 *
	 */
	public function testGc(): void
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('glob')->once()->with('/sessions/*')->andReturn(['/sessions/123', '/sessions/456']);

		$fileSystem->shouldReceive('lastModified')->once()->with('/sessions/123')->andReturn(2000000000);

		$fileSystem->shouldReceive('lastModified')->once()->with('/sessions/456')->andReturn(1983);

		$fileSystem->shouldReceive('isWritable')->once()->with('/sessions/456')->andReturn(true);

		$fileSystem->shouldReceive('remove')->once()->with('/sessions/456');

		$file = new File($fileSystem, '/sessions');

		$file->gc(1984);
	}
}
