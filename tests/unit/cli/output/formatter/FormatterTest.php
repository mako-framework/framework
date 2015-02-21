<?php

namespace mako\tests\unit\cli\output\formatter;

use \Exception;

use mako\cli\output\formatter\Formatter;

/**
 * @group unit
 */

class OutputTest extends \PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testConstructor()
	{
		$formatter = new Formatter;

		$this->assertInternalType('boolean', $formatter->hasAnsiSupport());

		$formatter = new Formatter(true);

		$this->assertTrue($formatter->hasAnsiSupport());

		$formatter = new Formatter(false);

		$this->assertFalse($formatter->hasAnsiSupport());
	}

	/**
	 *
	 */

	public function testBasicFormatter()
	{
		$formatter = new Formatter(true);

		$this->assertSame("\033[34mfoo\033[0m", $formatter->format('<blue>foo</blue>'));

		$this->assertSame("\033[34mfoo \033[32mbar\033[0m\033[34m baz\033[0m", $formatter->format('<blue>foo <green>bar</green> baz</blue>'));
	}

	/**
	 *
	 */

	public function testTagEscaping()
	{
		$formatter = new Formatter(true);

		$this->assertSame('<blue>foo</blue>', $formatter->format('\<blue>foo\</blue>'));
	}

	/**
	 *
	 */

	public function testCustomStyle()
	{
		$formatter = new Formatter(true);

		$formatter->addStyle('my_style', ['black', 'bg_green']);

		$this->assertSame("\033[30;42mfoo\033[0m", $formatter->format('<my_style>foo</my_style>'));
	}

	/**
	 *
	 */

	public function testFormattingWithoutAnsiSupport()
	{
		$formatter = new Formatter(false);

		$this->assertSame('foo', $formatter->format('<blue>foo</blue>'));
	}

	/**
	 *
	 */

	public function testEscape()
	{
		$formatter = new Formatter(true);

		$this->assertSame('\<blue>foo\</blue>', $formatter->escape('<blue>foo</blue>'));
	}

	/**
	 *
	 */

	public function testStrip()
	{
		$formatter = new Formatter(true);

		$this->assertSame('foo', $formatter->strip('<blue>foo</blue>'));
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 */

	public function testUndefinedTagException()
	{
		try
		{
			$formatter = new Formatter(true);

			$formatter->format('<fail>hello</fail>');
		}
		catch(Exception $e)
		{
			$this->assertSame('mako\cli\output\formatter\Formatter::getStyleCodes(): Undefined formatting tag [ fail ] detected.', $e->getMessage());

			throw $e;
		}
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 */

	public function testIncorrectTagNestingException()
	{
		try
		{
			$formatter = new Formatter(true);

			$formatter->format('<blue>he<green>llo</blue></green>');
		}
		catch(Exception $e)
		{
			$this->assertSame('mako\cli\output\formatter\Formatter::closeStyle(): Incorrectly nested formatting tag detected.', $e->getMessage());

			throw $e;
		}
	}

	/**
	 * @expectedException \mako\cli\output\formatter\FormatterException
	 */

	public function testMissingCloseTagException()
	{
		try
		{
			$formatter = new Formatter(true);

			$formatter->format('<blue>hello');
		}
		catch(Exception $e)
		{
			$this->assertSame('mako\cli\output\formatter\Formatter::format(): Missing formatting close tag detected.', $e->getMessage());

			throw $e;
		}
	}
}