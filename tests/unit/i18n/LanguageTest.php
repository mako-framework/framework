<?php

namespace mako\tests\unit\i18n;

use \mako\i18n\Language;

use \Mockery as m;

/**
 * @group unit
 */

class ConfigTest extends \PHPUnit_Framework_TestCase
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
		$fileSystem = m::mock('mako\file\FileSystem');

		$this->loadStrings($fileSystem);

		$this->loadInflection($fileSystem);

		return $fileSystem;
	}

	/**
	 * 
	 */

	public function loadStrings($fileSystem)
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
				'greeting'  => 'hello, %s!', 
				'goodbye'   => 'goodbye, %s',
				'pluralize' => 'You have %1$u new <pluralize:%1$u>message</pluralize>',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/strings/bar.php')->andReturn
		(
			[
				'foo' => 'bar', 
				'bar' => 'foo',
			]
		);

		//

		$fileSystem->shouldReceive('glob')->once()->with('/app/packages/*/i18n/en_US/strings/*.php', GLOB_NOSORT)->andReturn
		(
			[
				'/app/packages/foo/i18n/en_US/strings/foo.php', 
				'/app/packages/foo/i18n/en_US/strings/bar.php',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/foo/i18n/en_US/strings/foo.php')->andReturn
		(
			[
				'greeting' => 'hello, %s!', 
				'goodbye'  => 'goodbye, %s',
			]
		);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/packages/foo/i18n/en_US/strings/bar.php')->andReturn
		(
			[
				'foo' => 'bar', 
				'bar' => 'foo',
			]
		);
	}

	/**
	 * 
	 */

	public function loadInflection($fileSystem)
	{
		$inflection = 
		[
			'rules' => 
			[
				'plural' => 
				[
					'/$/' => "s",
				],

				'irregular' => 
				[
					
				],
			],

			'pluralize' => function($word, $count, $rules)
			{
				if($count !== 1)
				{
					foreach($rules['plural'] as $search => $replace)
					{
						if(preg_match($search, $word))
						{
							$word = preg_replace($search, $replace, $word);

							break;
						}
					}
				}

				return $word;
			},
		];

		$fileSystem->shouldReceive('exists')->once()->with('/app/i18n/en_US/inflection.php')->andReturn(true);

		$fileSystem->shouldReceive('includeFile')->once()->with('/app/i18n/en_US/inflection.php')->andReturn($inflection);
	}

	/**
	 * 
	 */

	public function testPluralize()
	{
		$fileSystem = $this->getFileSystem();

		$language = new Language($fileSystem, '/app', 'en_US');

		$this->assertEquals('apple', $language->pluralize('apple', 1));

		$this->assertEquals('apples', $language->pluralize('apple', 10));

		$this->assertEquals('apples', $language->pluralize('apple'));
	}

	/**
	 * 
	 */

	public function testHas()
	{
		$fileSystem = $this->getFileSystem();

		$language = new Language($fileSystem, '/app', 'en_US');

		$this->assertTrue($language->has('foo.greeting'));
		
		$this->assertTrue($language->has('foo.goodbye'));

		//
		
		$this->assertTrue($language->has('bar.foo'));
		
		$this->assertTrue($language->has('bar.bar'));

		//
		
		$this->assertTrue($language->has('foo::foo.greeting'));
		
		$this->assertTrue($language->has('foo::foo.goodbye'));

		//
		
		$this->assertTrue($language->has('foo::bar.foo'));
		
		$this->assertTrue($language->has('foo::bar.bar'));

		//

		$this->assertFalse($language->has('foo.xxx'));
		
		$this->assertFalse($language->has('bar.xxx'));
		
		$this->assertFalse($language->has('foo::foo.xxx'));
		
		$this->assertFalse($language->has('foo::bar.xxx'));
	}

	/**
	 * 
	 */

	public function testGet()
	{
		$fileSystem = $this->getFileSystem();

		$language = new Language($fileSystem, '/app', 'en_US');

		$this->assertEquals('hello, world!', $language->get('foo.greeting', ['world']));

		$this->assertEquals('goodbye, world', $language->get('foo.goodbye', ['world']));

		$this->assertEquals('foo.bax', $language->get('foo.bax'));

		//

		$this->assertEquals('bar', $language->get('bar.foo'));

		$this->assertEquals('foo', $language->get('bar.bar'));

		$this->assertEquals('bar.bax', $language->get('bar.bax'));

		//

		$this->assertEquals('hello, world!', $language->get('foo::foo.greeting', ['world']));

		$this->assertEquals('goodbye, world', $language->get('foo::foo.goodbye', ['world']));

		$this->assertEquals('foo::foo.bax', $language->get('foo::foo.bax'));

		//

		$this->assertEquals('bar', $language->get('foo::bar.foo'));

		$this->assertEquals('foo', $language->get('foo::bar.bar'));

		$this->assertEquals('foo::bar.bax', $language->get('foo::bar.bax'));

		//

		$this->assertEquals('You have 1 new message', $language->get('foo.pluralize', [1]));

		$this->assertEquals('You have 10 new messages', $language->get('foo.pluralize', [10]));
	}

	/**
	 * 
	 */

	public function testCache()
	{
		$fileSystem = m::mock('mako\file\FileSystem');

		$this->loadInflection($fileSystem);

		$cache = m::mock('mako\cache\Cache');

		$cache->shouldReceive('getOrElse')->once()/*->with('i18n:en_US', function(){}, 3600)*/->andReturn(['cached' => ['key' => 'value']]);

		$language = new Language($fileSystem, '/app', 'en_US', $cache);

		$this->assertEquals('value', $language->get('cached.key'));
	}
}