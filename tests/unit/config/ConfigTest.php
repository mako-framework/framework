<?php

namespace mako\tests\unit\config;

use \mako\config\Config;

use \Mockery as m;

/**
 * @group unit
 */

class ConfigTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	protected $makoEnv;

	/**
	 * 
	 */

	public function setUp()
	{
		// Clear Mako environment for tests

		$this->makoEnv = mako_env();

		if(!empty($this->makoEnv))
		{
			putenv('MAKO_ENV');
		}
	}

	/**
	 * 
	 */

	public function tearDown()
	{
		m::close();

		// Reset Mako environment

		if(!empty($this->makoEnv))
		{
			putenv('MAKO_ENV=' . $this->makoEnv);
		}
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

	public function testBasic()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$this->assertNull($config->get('settings.world'));

		$this->assertFalse($config->get('settings.world', false));
	}

	/**
	 * 
	 */

	public function testPackage()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('exists')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('hello', $config->get('baz::settings.greeting'));
	}

	/**
	 * 
	 */

	public function testPackageOverride()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/packages/baz/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/packages/baz/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('hello', $config->get('baz::settings.greeting'));
	}

	/**
	 * 
	 */

	public function testEvironmentOverride()
	{
		putenv('MAKO_ENV=dev');

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('konnichiwa', $config->get('settings.greeting'));

		$this->assertEquals('sayonara', $config->get('settings.goodbye'));

		putenv('MAKO_ENV');
	}

	/**
	 * 
	 */

	public function testPackageEvironmentOverride()
	{
		putenv('MAKO_ENV=dev');

		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('exists')->once()->with('/app/packages/baz/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/baz/config/settings.php')->andReturn(['greeting' => 'hello', 'goodbye' => 'sayonara']);

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/dev/packages/baz/settings.php')->andReturn(false);

		$fileSystem->shouldReceive('exists')->once()->with('/app/packages/baz/config/dev/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/baz/config/dev/settings.php')->andReturn(['greeting' => 'konnichiwa']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('konnichiwa', $config->get('baz::settings.greeting'));

		$this->assertEquals('sayonara', $config->get('baz::settings.goodbye'));

		putenv('MAKO_ENV');
	}

	/**
	 * 
	 */

	public function testSet()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/settings.php')->andReturn([]);

		$config = new Config($fileSystem, '/app');

		$this->assertNull($config->get('settings.greeting'));

		$config->set('settings.greeting', 'hello');

		$this->assertEquals('hello', $config->get('settings.greeting'));
	}

	/**
	 * 
	 */

	public function testRemove()
	{
		$fileSystem = $this->getFileSystem();

		$fileSystem->shouldReceive('exists')->once()->with('/app/config/settings.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/config/settings.php')->andReturn(['greeting' => 'hello']);

		$config = new Config($fileSystem, '/app');

		$this->assertEquals('hello', $config->get('settings.greeting'));

		$config->remove('settings.greeting');

		$this->assertNull($config->get('settings.greeting'));
	}
}