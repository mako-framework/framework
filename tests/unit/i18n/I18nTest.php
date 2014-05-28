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

	public function getLanguage()
	{
		return m::mock('mako\i18n\Language');
	}

	/**
	 * 
	 */

	public function testGetLanguage()
	{
		$i18n = new I18n($this->getFileSystem(), '/app', 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());
	}

	/**
	 * 
	 */

	public function testSetLanguage()
	{
		$i18n = new I18n($this->getFileSystem(), '/app', 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());

		$i18n->setLanguage('nb_NO');

		$this->assertEquals('nb_NO', $i18n->getLanguage());
	}

	/**
	 * 
	 */

	public function testHas()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('has')->once()->with('foo.bar')->andReturn(true);

		$language->shouldReceive('has')->once()->with('foo.baz')->andReturn(false);

		$i18n->shouldReceive('language')->twice()->with(null)->andReturn($language);

		$this->assertTrue($i18n->has('foo.bar'));

		$this->assertFalse($i18n->has('foo.baz'));
	}

	/**
	 * 
	 */

	public function testHasWithLanguage()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('has')->once()->with('foo.bar')->andReturn(true);

		$language->shouldReceive('has')->once()->with('foo.baz')->andReturn(false);

		$i18n->shouldReceive('language')->twice()->with('nb_NO')->andReturn($language);

		$this->assertTrue($i18n->has('foo.bar', 'nb_NO'));

		$this->assertFalse($i18n->has('foo.baz', 'nb_NO'));
	}

	/**
	 * 
	 */

	public function testGet()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('get')->once()->with('foo.bar', [])->andReturn('foo.bar');

		$i18n->shouldReceive('language')->once()->with(null)->andReturn($language);

		$this->assertEquals('foo.bar', $i18n->get('foo.bar'));
	}

	/**
	 * 
	 */

	public function testGetWithLanguage()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('get')->once()->with('foo.bar', [])->andReturn('foo.bar');

		$i18n->shouldReceive('language')->once()->with('nb_NO')->andReturn($language);

		$this->assertEquals('foo.bar', $i18n->get('foo.bar', [], 'nb_NO'));
	}

	/**
	 * 
	 */

	public function testPluralize()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('pluralize')->once()->with('apple', 0)->andReturn('apples');

		$language->shouldReceive('pluralize')->once()->with('apple', 1)->andReturn('apple');

		$i18n->shouldReceive('language')->twice()->with(null)->andReturn($language);

		$this->assertEquals('apples', $i18n->pluralize('apple'));

		$this->assertEquals('apple', $i18n->pluralize('apple', 1));
	}

	/**
	 * 
	 */

	public function testPluralizeWithLanguage()
	{
		$i18n = m::mock('mako\i18n\I18n[language]', [$this->getFileSystem(), '/app', 'en_US']);

		$i18n->shouldAllowMockingProtectedMethods();

		$language = $this->getLanguage();

		$language->shouldReceive('pluralize')->once()->with('eple', 0)->andReturn('epler');

		$language->shouldReceive('pluralize')->once()->with('eple', 1)->andReturn('eple');

		$i18n->shouldReceive('language')->twice()->with('nb_NO')->andReturn($language);

		$this->assertEquals('epler', $i18n->pluralize('eple', 0, 'nb_NO'));

		$this->assertEquals('eple', $i18n->pluralize('eple', 1, 'nb_NO'));
	}
}