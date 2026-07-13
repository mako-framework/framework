<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http;

use InvalidArgumentException;
use mako\http\response\CustomStatus;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CustomStatusTest extends TestCase
{
	/**
	 *
	 */
	public function testCustomStatus(): void
	{
		$status = new CustomStatus(299, 'A OK');

		$this->assertSame(299, $status->value);
		$this->assertSame(299, $status->getCode());
		$this->assertSame('A OK', $status->getMessage());
	}

	/**
	 *
	 */
	public function testInvalidCustomStatus(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('A HTTP status code must be between 100 and 599.');

		$status = new CustomStatus(600, 'Invalid status code');
	}
}
