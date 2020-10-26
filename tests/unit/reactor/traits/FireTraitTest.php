<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\traits;

use mako\reactor\traits\FireTrait;
use mako\tests\TestCase;

/**
 * @group unit
 */
class FireTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testBuildReactorPath(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar';
					}
				};
			}

			public function test()
			{
				return $this->buildReactorPath();
			}
		};

		$this->assertEquals(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor', $class->test());
	}

	/**
	 *
	 */
	public function testBuildCommandWithoutEnv(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return '/foo/bar';
					}

					public function getEnvironment()
					{
						return null;
					}
				};
			}

			public function test()
			{
				return $this->buildCommand('foobar --test=1');
			}
		};

		if(DIRECTORY_SEPARATOR === '/')
		{
			$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1';

			$this->assertEquals($command, $class->test());
		}
		else
		{
			$command = 'start ' . escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1';
		}
	}

	/**
	 *
	 */
	public function testBuildBackgroundCommandWithoutEnv(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return '/foo/bar';
					}

					public function getEnvironment()
					{
						return null;
					}
				};
			}

			public function test()
			{
				return $this->buildCommand('foobar --test=1', true);
			}
		};

		if(DIRECTORY_SEPARATOR === '/')
		{
			$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1 &';

			$this->assertEquals($command, $class->test());
		}
		else
		{
			$command = 'start /b ' . escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1';
		}
	}

	/**
	 *
	 */
	public function testBuildCommandWithEnv(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return '/foo/bar';
					}

					public function getEnvironment()
					{
						return 'dev';
					}
				};
			}

			public function test()
			{
				return $this->buildCommand('foobar --test=1');
			}
		};

		if(DIRECTORY_SEPARATOR === '/')
		{
			$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 ' . escapeshellarg('--env=dev') . ' 2>&1';

			$this->assertEquals($command, $class->test());
		}
		else
		{
			$command = 'start ' . escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 ' . escapeshellarg('--env=dev') . ' 2>&1';
		}
	}

	/**
	 *
	 */
	public function testBuildCommandWithEnvWithManualOverride(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return '/foo/bar';
					}

					public function getEnvironment()
					{
						return 'dev';
					}
				};
			}

			public function test()
			{
				return $this->buildCommand('foobar --test=1 --env=prod');
			}
		};

		if(DIRECTORY_SEPARATOR === '/')
		{
			$command =escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 --env=prod 2>&1';

			$this->assertEquals($command, $class->test());
		}
		else
		{
			$command = 'start ' .escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . '  foobar --test=1 --env=prod 2>&1';
		}
	}

	/**
	 *
	 */
	public function testBuildCommandWithEnvWithoutUsingSame(): void
	{
		$class = new class
		{
			use FireTrait;

			protected $app;

			public function __construct()
			{
				$this->app = new class
				{
					public function getPath()
					{
						return '/foo/bar';
					}

					public function getEnvironment()
					{
						return 'dev';
					}
				};
			}

			public function test()
			{
				return $this->buildCommand('foobar --test=1', false, false);
			}
		};

		if(DIRECTORY_SEPARATOR === '/')
		{
			$command = escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1';

			$this->assertEquals($command, $class->test());
		}
		else
		{
			$command = 'start ' . escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg(DIRECTORY_SEPARATOR . 'foo' . DIRECTORY_SEPARATOR . 'bar' . DIRECTORY_SEPARATOR . 'reactor') . ' foobar --test=1 2>&1';
		}
	}
}
