<?php

namespace mako\tests\unit\i18n;

use \mako\i18n\I18n;

use \Mockery as m;

/**
 * @group unit
 */

class I18nTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * 
	 */

	protected $strings = 
	[
		'foo'	   => ['foo' => 'foostring', 'greeting' => 'hello %s'],
		'bar'      => ['bar' => 'barstring', 'pluralization' => 'You have %1$u <pluralize:%1$u>apple</pluralize>.'],
		'baz::baz' => ['baz' => 'bazstring'],
	];

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

	public function getLoader()
	{
		return m::mock('mako\i18n\Loader');
	}

	/**
	 * 
	 */

	protected function loadStrings($loader, $lang = 'en_US')
	{
		$loader->shouldReceive('loadStrings')->once()->with($lang)->andReturn($this->strings);

		return $loader;
	}

	/**
	 * 
	 */

	protected function loadInflection($loader)
	{
		$loader->shouldReceive('loadInflection')->once()->with('en_US')->andReturn
		(
			[
				'rules' => '',
				'pluralize' => function($string)
				{
					return str_replace('apple', 'apples', $string);
				},
			]
		);
		return $loader;
	}

	/**
	 * 
	 */

	protected function getCache()
	{
		return m::mock('mako\cache\Cache');
	}

	/**
	 * 
	 */

	public function loadFromCache($cache)
	{
		$cache->shouldReceive('get')->once()->with('mako.i18n.en_US')->andReturn($this->strings);

		return $cache;
	}

	/**
	 * 
	 */

	public function saveToCache($cache)
	{
		$cache->shouldReceive('get')->once()->with('mako.i18n.en_US')->andReturn(false);

		$cache->shouldReceive('put')->once()->with('mako.i18n.en_US', $this->strings, 3600);

		return $cache;
	}

	/**
	 * 
	 */

	public function testGetLanguage()
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());
	}

	/**
	 * 
	 */

	public function testSetLanguage()
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());

		$i18n->setLanguage('nb_NO');

		$this->assertEquals('nb_NO', $i18n->getLanguage());
	}

	/**
	 * 
	 */

	public function testGetLoader()
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertInstanceOf('mako\i18n\Loader', $i18n->getLoader());
	}

	/**
	 * 
	 */

	public function testHas()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader()), 'en_US');

		$this->assertTrue($i18n->has('foo.foo'));

		$this->assertTrue($i18n->has('bar.bar'));

		$this->assertTrue($i18n->has('baz::baz.baz'));

		$this->assertFalse($i18n->has('foo.nope'));

		$this->assertFalse($i18n->has('bar.nope'));

		$this->assertFalse($i18n->has('baz::baz.nope'));
	}

	/**
	 * @expectedException \RuntimeException
	 */

	public function testException()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader()), 'en_US');

		$i18n->get('nope.foo');
	}

	/**
	 * 
	 */

	public function testGet()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader()), 'en_US');

		$this->assertEquals('foostring', $i18n->get('foo.foo'));

		$this->assertEquals('barstring', $i18n->get('bar.bar'));

		$this->assertEquals('bazstring', $i18n->get('baz::baz.baz'));
	}

	/**
	 * 
	 */

	public function testGetWithLang()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader(), 'nb_NO'), 'en_US');

		$this->assertEquals('foostring', $i18n->get('foo.foo', [], 'nb_NO'));

		$this->assertEquals('barstring', $i18n->get('bar.bar', [], 'nb_NO'));

		$this->assertEquals('bazstring', $i18n->get('baz::baz.baz', [], 'nb_NO'));
	}

	/**
	 * 
	 */

	public function testParameters()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader()), 'en_US');

		$this->assertEquals('hello world', $i18n->get('foo.greeting', ['world']));
	}

	/**
	 * 
	 */

	public function testPluralize()
	{
		$i18n = new I18n($this->loadInflection($this->getLoader()), 'en_US');

		$this->assertEquals('apples', $i18n->pluralize('apple'));
	}

	/**
	 * 
	 */

	public function testPluralizeInStrings()
	{
		$i18n = new I18n($this->loadInflection($this->loadStrings($this->getLoader())), 'en_US');

		$this->assertEquals('You have 10 apples.', $i18n->get('bar.pluralization', [10]));
	}

	/**
	 * 
	 */

	public function testCacheLoad()
	{
		$i18n = new I18n($this->getLoader(), 'en_US', $this->loadFromCache($this->getCache()));

		$this->assertEquals('foostring', $i18n->get('foo.foo'));
	}

	/**
	 * 
	 */

	public function testCacheSave()
	{
		$i18n = new I18n($this->loadStrings($this->getLoader()), 'en_US', $this->saveToCache($this->getCache()));

		$this->assertEquals('foostring', $i18n->get('foo.foo'));
	}
}