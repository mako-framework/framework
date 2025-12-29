<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\formatter;

use mako\cli\output\formatter\exceptions\FormatterException;
use mako\cli\output\formatter\Formatter;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FormatterTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicFormatter(): void
	{
		$formatter = new Formatter;

		$this->assertSame("\x1b[34mfoo\x1b[0m", $formatter->format('<blue>foo</blue>'));

		$this->assertSame("\x1b[34mfoo \x1b[32mbar\x1b[0m\x1b[34m baz\x1b[0m", $formatter->format('<blue>foo <green>bar</green> baz</blue>'));
	}

	/**
	 *
	 */
	public function testTagEscaping(): void
	{
		$formatter = new Formatter;

		$this->assertSame('<blue>foo</blue>', $formatter->format('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testCustomStyle(): void
	{
		$formatter = new Formatter;

		$formatter->addStyle('my_style', ['black', 'bg_green']);

		$this->assertSame("\x1b[30;42mfoo\x1b[0m", $formatter->format('<my_style>foo</my_style>'));
	}

	/**
	 *
	 */
	public function testEscape(): void
	{
		$formatter = new Formatter;

		$this->assertSame('\<blue>foo\</blue>', $formatter->escape('<blue>foo</blue>'));
	}

	/**
	 *
	 */
	public function testStripTags(): void
	{
		$formatter = new Formatter;

		$this->assertSame('foo', $formatter->stripTags('<blue>foo</blue>'));

		$this->assertSame('\<blue>foo\</blue>', $formatter->stripTags('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testStripSGR(): void
	{
		$formatter = new Formatter;

		$this->assertSame('foo', $formatter->stripSGR($formatter->format('<blue>foo</blue>')));
	}

	/**
	 *
	 */
	public function testUndefinedTagException(): void
	{
		$this->expectException(FormatterException::class);

		$this->expectExceptionMessage('Undefined formatting tag [ fail ] detected.');

		$formatter = new Formatter;

		$formatter->format('<fail>hello</fail>');
	}

	/**
	 *
	 */
	public function testIncorrectTagNestingException(): void
	{
		$this->expectException(FormatterException::class);

		$this->expectExceptionMessage('Detected incorrectly nested formatting tag.');

		$formatter = new Formatter;

		$formatter->format('<blue>he<green>llo</blue></green>');
	}

	/**
	 *
	 */
	public function testMissingCloseTagException(): void
	{
		$this->expectException(FormatterException::class);

		$this->expectExceptionMessage('Detected missing formatting close tag');

		$formatter = new Formatter;

		$formatter->format('<blue>hello');
	}
}
