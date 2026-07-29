<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\security\signer;

use mako\security\signer\exceptions\SignerException;
use mako\security\signer\Signer;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class SignerTest extends TestCase
{
	/**
	 *
	 */
	public function testSign(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals(strlen($signed), strlen($string) + 64);

		$this->assertEquals($string, substr($signed, 64));
	}

	/**
	 *
	 */
	public function testValidateValid(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals($string, $signer->validate($signed));
	}

	/**
	 *
	 */
	public function testValidateInvalid(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$this->assertFalse($signer->validate(str_repeat('0', 64) . $string));
	}

	/**
	 *
	 */
	public function testValidateOrThrowValid(): void
	{
		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signed = $signer->sign($string);

		$this->assertEquals($string, $signer->validateOrThrow($signed));
	}

	/**
	 *
	 */
	public function testValidateOrThrowInvalid(): void
	{
		$this->expectException(SignerException::class);
		$this->expectExceptionMessageIs('Failed to validate the signed string. The signature is invalid or the data has been tampered with.');

		$string = 'hello, world!';

		$signer = new Signer('foobar');

		$signer->validateOrThrow(str_repeat('0', 64) . $string);
	}
}
