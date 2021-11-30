<?php

/**
 * @copyright Frederic G. Østby
 * @license	http://www.makoframework.com/license
 */

namespace mako\tests\unit\view\renderers\traits;

use mako\tests\TestCase;
use mako\view\renderers\traits\EscaperTrait;

/**
 * @group unit
 */
class EscaperTraitTest extends TestCase
{
	/**
	 *
	 */
	protected function getTraitUser()
	{
		return new class
		{
			use EscaperTrait;
		};
	}

	/**
	 *
	 */
	public function testThatEscapeHTMLAllowsNull(): void
	{
		$this->assertSame('', $this->getTraitUser()->escapeHTML(null, 'UTF-8'));
	}

	/**
	 *
	 */
	public function testThatEscapeURLAllowsNull(): void
	{
		$this->assertSame('', $this->getTraitUser()->escapeURL(null, 'UTF-8'));
	}

	/**
	 *
	 */
	public function testThatEscapeAttributeAllowsNull(): void
	{
		$this->assertSame('', $this->getTraitUser()->escapeAttribute(null, 'UTF-8'));
	}

	/**
	 *
	 */
	public function testThatEscapeCSSAllowsNull(): void
	{
		$this->assertSame('', $this->getTraitUser()->escapeCSS(null, 'UTF-8'));
	}

	/**
	 *
	 */
	public function testThatEscapeJavascriptAllowsNull(): void
	{
		$this->assertSame('', $this->getTraitUser()->escapeJavascript(null, 'UTF-8'));
	}
}
