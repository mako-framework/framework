<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\output\formatter;

use mako\cli\output\formatter\Formatter;
use mako\tests\TestCase;

/**
 * @group unit
 */
class OutputTest extends TestCase
{
	/**
	 *
	 */
	public function testBasicFormatter()
	{
		$formatter = new Formatter();

		$this->assertSame("\033[34mfoo\033[0m", $formatter->format('<blue>foo</blue>'));

		$this->assertSame("\033[34mfoo \033[32mbar\033[0m\033[34m baz\033[0m", $formatter->format('<blue>foo <green>bar</green> baz</blue>'));
	}

	/**
	 *
	 */
	public function testTagEscaping()
	{
		$formatter = new Formatter();

		$this->assertSame('<blue>foo</blue>', $formatter->format('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testCustomStyle()
	{
		$formatter = new Formatter();

		$formatter->addStyle('my_style', ['black', 'bg_green']);

		$this->assertSame("\033[30;42mfoo\033[0m", $formatter->format('<my_style>foo</my_style>'));
	}

	/**
	 *
	 */
	public function testEscape()
	{
		$formatter = new Formatter();

		$this->assertSame('\<blue>foo\</blue>', $formatter->escape('<blue>foo</blue>'));
	}

	/**
	 *
	 */
	public function testStripTags()
	{
		$formatter = new Formatter();

		$this->assertSame('foo', $formatter->stripTags('<blue>foo</blue>'));

		$this->assertSame('\<blue>foo\</blue>', $formatter->stripTags('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */
	public function testStripSGR()
	{
		$formatter = new Formatter();

		$this->assertSame('foo', $formatter->stripSGR($formatter->format('<blue>foo</blue>')));
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 * @expectedExceptionMessage Undefined formatting tag [ fail ] detected.
	 */
	public function testUndefinedTagException()
	{
		$formatter = new Formatter();

		$formatter->format('<fail>hello</fail>');
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 * @expectedExceptionMessage Detected incorrectly nested formatting tag.
	 */
	public function testIncorrectTagNestingException()
	{
		$formatter = new Formatter();

		$formatter->format('<blue>he<green>llo</blue></green>');
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 * @expectedExceptionMessage Detected missing formatting close tag.
	 */
	public function testMissingCloseTagException()
	{
		$formatter = new Formatter();

		$formatter->format('<blue>hello');
	}
}
