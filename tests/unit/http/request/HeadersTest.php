<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\request;

use mako\http\request\Headers;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class HeadersTest extends TestCase
{
	/**
	 * Returns [header value, expected acceptable content types].
	 */
	public static function acceptProvider(): array
	{
		return [
			'mixed quality values' => [
				'text/html,application/xhtml+xml,foo/bar; q=0.1,application/xml;q=0.9,image/webp,*/*;q=0.8',
				['text/html', 'application/xhtml+xml', 'image/webp', 'application/xml', '*/*', 'foo/bar'],
			],
			'semicolon inside quoted parameter' => [
				'text/html;title="a;b";q=0.2, application/json;q=0.8',
				['application/json', 'text/html'],
			],
			'escaped quotes with embedded comma' => [
				'application/example;note="say \"hello, world\"";q=0.8, text/html;q=0.5',
				['application/example', 'text/html'],
			],
			'explicitly unacceptable media type' => [
				'text/html;q=0, application/json;q=0.8',
				['application/json'],
			],
			'empty entries between commas' => [
				'text/html,,application/json,',
				['text/html', 'application/json'],
			],
			'invalid quality string' => [
				'text/html;q=garbage',
				[],
			],
			'quality greater than one' => [
				'text/html;q=1.5',
				[],
			],
			'quality with too many decimal places' => [
				'text/html;q=0.1234',
				[],
			],
			'duplicate quality parameter' => [
				'text/html;q=0.3;q=0.8',
				[],
			],
			'missing quality value' => [
				'text/html;q=',
				[],
			],
			'missing parameter value' => [
				'text/html;level=',
				[],
			],
			'unterminated quoted string' => [
				'text/html;title="unfinished',
				[],
			],
			'trailing semicolon' => [
				'text/html;',
				[],
			],
			'newline in header' => [
				"text/html\r\nX-Injected: value",
				[],
			],
			'header exceeding size limit' => [
				str_repeat('a', 16385),
				[],
			],
			'Simple list' => [
				'text/html, application/json',
				['text/html', 'application/json'],
			],
			'descending quality order' => [
				'text/html;q=0.3, application/json;q=0.9',
				['application/json', 'text/html'],
			],
			'stable order for equal quality' => [
				'text/html;q=0.8, application/json;q=0.8',
				['text/html', 'application/json'],
			],
			'missing quality defaults to one' => [
				'text/html;q=0.9, application/json',
				['application/json', 'text/html'],
			],
			'uppercase quality parameter' => [
				'text/html;Q=0.3, application/json;q=0.9',
				['application/json', 'text/html'],
			],
			'valid quality of exactly one' => [
				'text/html;q=1.000',
				['text/html'],
			],
			'empty header' => [
				'',
				[],
			],
		];
	}

	/**
	 * Returns [header value, expected acceptable charsets].
	 */
	public static function acceptCharsetProvider(): array
	{
		return [
			'mixed quality values' => [
				'UTF-8,FOO-1; q=0.1,UTF-16;q=0.9',
				['UTF-8', 'UTF-16', 'FOO-1'],
			],
		];
	}

	/**
	 * Returns [header value, expected acceptable encodings].
	 */
	public static function acceptEncodingProvider(): array
	{
		return [
			'mixed quality values' => [
				'gzip,foobar;q=0.1,deflate,sdch',
				['gzip', 'deflate', 'sdch', 'foobar'],
			],
		];
	}

	/**
	 * Returns [header value, expected acceptable languages].
	 */
	public static function acceptLanguageProvider(): array
	{
		return [
			'mixed quality values' => [
				'en-US,en;q=0.8,da;q=0.6,fr;q=0.4,foo; q=0.1,nb;q=0.2,sv;q=0.2',
				['en-US', 'en', 'da', 'fr', 'nb', 'sv', 'foo'],
			],
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
	#[DataProvider('acceptProvider')]
	public function testAcceptableContentTypes(string $header, array $expected): void
	{
		$headers = new Headers(['ACCEPT' => $header]);

		$this->assertSame($expected, $headers->getAcceptableContentTypes());
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
	#[DataProvider('acceptLanguageProvider')]
	public function testAcceptableLanguages(string $header, array $expected): void
	{
		$headers = new Headers(['ACCEPT_LANGUAGE' => $header]);

		$this->assertSame($expected, $headers->getAcceptableLanguages());
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
	#[DataProvider('acceptCharsetProvider')]
	public function testAcceptableCharsets(string $header, array $expected): void
	{
		$headers = new Headers(['ACCEPT_CHARSET' => $header]);

		$this->assertSame($expected, $headers->getAcceptableCharsets());
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
	#[DataProvider('acceptEncodingProvider')]
	public function testAcceptableEncodings(string $header, array $expected): void
	{
		$headers = new Headers(['ACCEPT_ENCODING' => $header]);

		$this->assertSame($expected, $headers->getAcceptableEncodings());
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
