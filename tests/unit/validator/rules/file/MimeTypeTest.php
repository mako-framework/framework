<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\MimeType;

/**
 * @group unit
 */
class MimeTypeTest extends TestCase
{
	/**
	 *
	 */
	public function setUp(): void
	{
		if(function_exists('finfo_open') === false)
		{
			$this->markTestSkipped("The fileinfo extension hasn't been enabled.");
		}
	}

	/**
	 *
	 */
	public function testValidatesWhenEmpty(): void
	{
		$rule = new MimeType('image/png');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new MimeType('image/png');

		$this->assertTrue($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));

		//

		$rule = new MimeType(['image/jpeg', 'image/png']);

		$this->assertTrue($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new MimeType(['text/plain', 'application/json']);

		$this->assertFalse($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));

		$this->assertSame('The foobar must be a file of type: text/plain, application/json.', $rule->getErrorMessage('foobar'));
	}
}
