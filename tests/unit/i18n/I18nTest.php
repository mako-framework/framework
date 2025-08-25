<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\i18n;

use mako\i18n\exceptions\I18nException;
use mako\i18n\I18n;
use mako\i18n\loaders\LoaderInterface;
use mako\tests\TestCase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class I18nTest extends TestCase
{
	/**
	 *
	 */
	protected array $strings =
	[
		'foo'	   => ['foo' => 'foostring', 'greeting' => 'hello %s'],
		'bar'      => ['bar' => 'barstring', 'pluralization' => 'You have %1$u <pluralize:%1$u>apple</pluralize>.'],
		'baz'      => ['number1' => 'You have <number>%s</number> apples.', 'number2' => '<number:3>%s</number>', 'number3' => '<number:3,:>%s</number>', 'number4' => '<number:3,:,;>%s</number>'],
		'baz::baz' => ['baz' => 'bazstring'],
		'nested'   => ['foo' => ['bar' => 'baz']],
	];

	/**
	 *
	 */
	public function getLoader(): LoaderInterface&MockInterface
	{
		return Mockery::mock(LoaderInterface::class);
	}

	/**
	 *
	 */
	public function testGetLanguage(): void
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());
	}

	/**
	 *
	 */
	public function testSetLanguage(): void
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertEquals('en_US', $i18n->getLanguage());

		$i18n->setLanguage('nb_NO');

		$this->assertEquals('nb_NO', $i18n->getLanguage());
	}

	/**
	 *
	 */
	public function testGetLoader(): void
	{
		$i18n = new I18n($this->getLoader(), 'en_US');

		$this->assertInstanceOf(LoaderInterface::class, $i18n->getLoader());
	}

	/**
	 *
	 */
	public function testHas(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'bar')->andReturn($this->strings['bar']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'baz::baz')->andReturn($this->strings['baz::baz']);

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'nested')->andReturn($this->strings['nested']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertTrue($i18n->has('foo.foo'));

		$this->assertTrue($i18n->has('bar.bar'));

		$this->assertTrue($i18n->has('baz::baz.baz'));

		$this->assertTrue($i18n->has('nested.foo.bar'));

		$this->assertFalse($i18n->has('foo.nope'));

		$this->assertFalse($i18n->has('bar.nope'));

		$this->assertFalse($i18n->has('baz::baz.nope'));

		$this->assertFalse($i18n->has('nested.foo'));
	}

	/**
	 *
	 */
	public function testGet(): void
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
	public function testGetWithLang(): void
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
	public function testParameters(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'foo')->andReturn($this->strings['foo']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('hello world', $i18n->get('foo.greeting', ['world']));
	}

	/**
	 *
	 */
	public function testPluralize(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadInflection')->once()->with('en_US')->andReturn
		(
			[
				'rules' => '',
				'pluralize' => function ($string) {
					return str_replace('apple', 'apples', $string);
				},
			]
		);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('apples', $i18n->pluralize('apple'));
	}

	/**
	 *
	 */
	public function testNumber(): void
	{
		$loader = $this->getLoader();

		$i18n = new I18n($loader, 'en_US');

		$this->assertSame('1,234', $i18n->number(1234.123));

		$this->assertSame('1,234.123', $i18n->number(1234.123, 3));

		$this->assertSame('1;234:123', $i18n->number(1234.123, 3, ':', ';'));
	}

	/**
	 *
	 */
	public function testPluralizeWithoutPluralizationRules(): void
	{
		$this->expectException(I18nException::class);

		$this->expectExceptionMessage('The [ en_US ] language pack does not include any inflection rules.');

		$loader = $this->getLoader();

		$loader->shouldReceive('loadInflection')->once()->with('en_US')->andReturn(null);

		$i18n = new I18n($loader, 'en_US');

		$i18n->pluralize('apple');
	}

	/**
	 *
	 */
	public function testPluralizeInStrings(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'bar')->andReturn($this->strings['bar']);

		$loader->shouldReceive('loadInflection')->once()->with('en_US')->andReturn
		(
			[
				'rules'     => '',
				'pluralize' => function ($string) {
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
	public function testFormatNumbersInStrings(): void
	{
		$loader = $this->getLoader();

		$loader->shouldReceive('loadStrings')->once()->with('en_US', 'baz')->andReturn($this->strings['baz']);

		$i18n = new I18n($loader, 'en_US');

		$this->assertEquals('You have 1,234 apples.', $i18n->get('baz.number1', [1234.123]));

		$this->assertEquals('1,234.123', $i18n->get('baz.number2', [1234.123]));

		$this->assertEquals('1,234:123', $i18n->get('baz.number3', [1234.123]));

		$this->assertEquals('1;234:123', $i18n->get('baz.number4', [1234.123]));
	}

	/**
	 *
	 */
	public function testHasWithoutValidKey(): void
	{
		$loader = $this->getLoader();

		$i18n = new I18n($loader, 'en_US');

		$this->assertFalse($i18n->has('foobar'));
	}

	/**
	 *
	 */
	public function testGetWithoutValidKey(): void
	{
		$loader = $this->getLoader();

		$i18n = new I18n($loader, 'en_US');

		$this->assertSame('foobar', $i18n->get('foobar'));
	}
}
