<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\file;

use mako\file\FileInfo;
use mako\tests\TestCase;
use mako\validator\rules\file\Mimetype;

/**
 * @group unit
 */
class MimetypeTest extends TestCase
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
		$rule = new Mimetype('image/png');

		$this->assertFalse($rule->validateWhenEmpty());
	}

	/**
	 *
	 */
	public function testWithValidValue(): void
	{
		$rule = new Mimetype('image/png');

		$this->assertTrue($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));

		//

		$rule = new Mimetype(['image/jpeg', 'image/png']);

		$this->assertTrue($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));
	}

	/**
	 *
	 */
	public function testWithInvalidValue(): void
	{
		$rule = new Mimetype(['text/plain', 'application/json']);

		$this->assertFalse($rule->validate(new FileInfo(__DIR__ . '/fixtures/png.png'), []));

		$this->assertSame('The foobar must be a file of type: text/plain, application/json.', $rule->getErrorMessage('foobar'));
	}
}
