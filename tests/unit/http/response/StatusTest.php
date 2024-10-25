<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use mako\http\response\Status;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use ValueError;

#[Group('unit')]
class StatusTest extends TestCase
{
	/**
	 *
	 */
	public function testStatus(): void
	{
		$this->assertSame(Status::OK, Status::from(200));
		$this->assertSame('OK', Status::OK->getMessage());
		$this->assertSame(200, Status::OK->getCode());
	}

	/**
	 *
	 */
	public function testInvalidStatus(): void
	{
		$this->expectException(ValueError::class);
		$this->expectExceptionMessageMatches('/^999 is not a valid backing value for enum/');

		Status::from(999);
	}
}
