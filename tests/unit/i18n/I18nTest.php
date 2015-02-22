<?php

namespace mako\tests\unit\i18n;

use mako\i18n\I18n;

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

	protected function getCache()
	{
		return m::mock('mako\cache\Cache');
	}

	/**
	 *
	 */

	public function testSetCache()
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$i18n->setCache($this->getCache());
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
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'bar')->andReturn($this->strings['bar']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'baz::baz')->andReturn($this->strings['baz::baz']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertTrue($i18n->has('foo.foo'));

		$this->assertTrue($i18n->has('bar.bar'));

		$this->assertTrue($i18n->has('baz::baz.baz'));

		$this->assertFalse($i18n->has('foo.nope'));

		$this->assertFalse($i18n->has('bar.nope'));

		$this->assertFalse($i18n->has('baz::baz.nope'));
	}

	/**
	 *
	 */

	public function testGet()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'bar')->andReturn($this->strings['bar']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'baz::baz')->andReturn($this->strings['baz::baz']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('foostring', $i18n->get('foo.foo'));

		$this->assertEquals('barstring', $i18n->get('bar.bar'));

		$this->assertEquals('bazstring', $i18n->get('baz::baz.baz'));
	}

	/**
	 *
	 */

	public function testGetWithLang()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('nb_NO', 'foo')->andReturn($this->strings['foo']);

		$loader->shouldReceive('loadStrings')->once()->with('nb_NO', 'bar')->andReturn($this->strings['bar']);

		$loader->shouldReceive('loadStrings')->once()->with('nb_NO', 'baz::baz')->andReturn($this->strings['baz::baz']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('foostring', $i18n->get('foo.foo', [], 'nb_NO'));

		$this->assertEquals('barstring', $i18n->get('bar.bar', [], 'nb_NO'));

		$this->assertEquals('bazstring', $i18n->get('baz::baz.baz', [], 'nb_NO'));
	}

	/**
	 *
	 */

	public function testParameters()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('hello world', $i18n->get('foo.greeting', ['world']));
	}

	/**
	 *
	 */

	public function testPluralize()
	{
		$loader = $this->getLoader();

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

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('apples', $i18n->pluralize('apple'));
	}

	/**
	 * @expectedException \RuntimeException
	 * @expectedExceptionMessage mako\i18n\I18n::pluralize(): The [ en_US ] language pack does not include any inflection rules.
	 */

	public function testPluralizeWithoutPluralizationRules()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadInflection')->once()->with('en_US')->andReturn(null);

		$i18n = new I18n($loader, 'en_US');

		$i18n->pluralize('apple');
	}

	/**
	 *
	 */

	public function testPluralizeInStrings()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'bar')->andReturn($this->strings['bar']);

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

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('You have 10 apples.', $i18n->get('bar.pluralization', [10]));
	}

	/**
	 *
	 */

	public function testCacheLoad()
	{
		$cache = $this->getCache();

		$cache->shouldReceive('get')->once()->with('mako.i18n.en_US')->andReturn($this->strings);

		$i18n = new I18n($this->getLoader(), 'en_US', $cache);

		$this->assertEquals('foostring', $i18n->get('foo.foo'));
	}

	/**
	 *
	 */

	public function testCacheSave()
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$cache = $this->getCache();

		$cache->shouldReceive('get')->once()->with('mako.i18n.en_US')->andReturn(false);

		$cache->shouldReceive('put')->once()->with('mako.i18n.en_US', ['foo' => $this->strings['foo']], 3600);

		$i18n = new I18n($loader, 'en_US', $cache);

		$this->assertEquals('foostring', $i18n->get('foo.foo'));
	}
}