<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Headers;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HeadersTest extends TestCase
{
	/**
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
	public function testCountEmptySet(): void
	{
		$headers = new Headers;

		$this->assertSame(0, count($headers));
	}

	/**
	 *
	 */
	public function testCountSet(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(1, count($headers));
	}

	/**
	 *
	 */
	public function testIterateEmpySet(): void
	{
		$headers = new Headers;

		$iterations = 0;

		foreach ($headers as $header) {
			$iterations++;
		}

		$this->assertSame(0, $iterations);
	}

	/**
	 *
	 */
	public function testIterateSet(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$iterations = 0;

		foreach ($headers as $header) {
			$iterations++;
		}

		$this->assertSame(1, $iterations);
	}

	/**
	 *
	 */
	public function testAdd(): void
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
	public function testHas(): void
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
	public function testGet(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame('bar', $headers->get('foo'));

		$this->assertNull($headers->get('bar'));

		$this->assertFalse($headers->get('bar', false));
	}

	/**
	 *
	 */
	public function testRemove(): void
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
	public function testAll(): void
	{
		$headers = new Headers(['FOO' => 'bar']);

		$this->assertSame(['FOO' => 'bar'], $headers->all());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypes(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'], $headers->getAcceptableContentTypes());
	}

	/**
	 *
	 */
	public function testAcceptableContentTypesWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->getAcceptableContentTypes('default'));
	}

	/**
	 *
	 */
	public function testAcceptableLanguages(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'], $headers->getAcceptableLanguages());
	}

	/**
	 *
	 */
	public function testAcceptableLanguagesWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->getAcceptableLanguages('default'));
	}

	/**
	 *
	 */
	public function testAcceptableCharsets(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['UTF-8', 'UTF-16', 'FOO-1'], $headers->getAcceptableCharsets());
	}

	/**
	 *
	 */
	public function acceptableCharsetsWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->getAcceptableCharsets('default'));
	}

	/**
	 *
	 */
	public function testAcceptableEncodings(): void
	{
		$headers = new Headers($this->getAcceptHeaders());

		$this->assertSame(['gzip', 'deflate', 'sdch', 'foobar'], $headers->getAcceptableEncodings());
	}

	/**
	 *
	 */
	public function testAcceptableEncodingsWithNoHeaders(): void
	{
		$headers = new Headers;

		$this->assertSame(['default'], $headers->getAcceptableEncodings('default'));
	}

	/**
	 *
	 */
	public function testGetBearerToken(): void
	{
		$headers = new Headers(['AUTHORIZATION' => 'Bearer foobar']);

		$this->assertSame('foobar', $headers->getBearerToken());

		//

		$headers = new Headers(['AUTHORIZATION' => 'Bearerfoobar']);

		$this->assertNull($headers->getBearerToken());

		//

		$headers = new Headers;

		$this->assertNull($headers->getBearerToken());
	}
}
