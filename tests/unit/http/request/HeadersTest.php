<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use PHPUnit_Framework_TestCase;

use mako\http\request\Headers;

/**
 * @group unit
 */
class HeadersTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 *
	 */
	protected function getAcceptHeaders()
	{
		return
		[
			'ACCEPT'          => 'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
			'ACCEPT_CHARSET'  => 'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
			'ACCEPT_ENCODING' => 'gzip,foobar;q=0.1,deflate,sdch',
			'ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
		];
	}
	/**
	 *
	 */
	public function testCountEmptySet()
	{
		$headers = new Headers;

		$this->assertSame(0, count($headers));
	}

	/**
	 *
	 */
	public function testCountSet()
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(1, count($headers));
	}

	/**
	 *
	 */
	public function testIterateEmpySet()
	{
		$headers = new Headers;

		$iterations = 0;

		foreach($headers as $header)
		{
			$iterations++;
		}

		$this->assertSame(0, $iterations);
	}

	/**
	 *
	 */
	public function testIterateSet()
	{
		$headers = new Headers(['FOO' => 'bar']);

		$iterations = 0;

		foreach($headers as $header)
		{
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testAdd()
	{
		$headers = new Headers;

		$this->assertSame(0, count($headers));

		$headers->add('foo', 'bar');

		$headers->add('foo-bar', 'foobar');

		$this->assertSame(2, count($headers));

		$this->assertSame(['FOO' => 'bar', 'FOO_BAR' => 'foobar'], $headers->all());
	}

	/**
	 *
	 */
	public function testHas()
	{
		$headers = new Headers(['FOO' => 'bar', 'FOO_BAR' => 'foobar']);

		$this->assertTrue($headers->has('foo'));

		$this->assertTrue($headers->has('FOO'));

		$this->assertTrue($headers->has('foo-bar'));

		$this->assertTrue($headers->has('foo_bar'));

		$this->assertFalse($headers->has('bar'));
	}

	/**
	 *
	 */
	public function testGet()
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame('bar', $headers->get('foo'));

		$this->assertNull($headers->get('bar'));

		$this->assertFalse($headers->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove()
	{
		$headers = new Headers(['FOO' => 'bar', 'FOO_BAR' => 'foobar']);

		$this->assertSame(2, count($headers));

		$headers->remove('foo');

		$this->assertSame(1, count($headers));

		$headers->remove('foo-bar');

		$this->assertSame(0, count($headers));
	}

	/**
	 *
	 */
	public function testAll()
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(['FOO' => 'bar'], $headers->all());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypes()
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'], $headers->acceptableContentTypes());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypesWithNoHeaders()
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableContentTypes('default'));
	}

	/**
	 *
	 */
	public function testAcceptableLanguages()
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'], $headers->acceptableLanguages());
	}

	/**
	 *
	 */
	public function testAcceptableLanguagesWithNoHeaders()
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableLanguages('default'));
	}

	/**
	 *
	 */
	public function testAcceptableCharsets()
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['UTF-8', 'UTF-16', 'FOO-1'], $headers->acceptableCharsets());
	}

	/**
	 *
	 */
	public function acceptableCharsetsWithNoHeaders()
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableCharsets('default'));
	}

	/**
	 *
	 */
	public function testAcceptableEncodings()
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['gzip', 'deflate', 'sdch', 'foobar'], $headers->acceptableEncodings());
	}

	/**
	 *
	 */
	public function testAcceptableEncodingsWithNoHeaders()
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->acceptableEncodings('default'));
	}
}
