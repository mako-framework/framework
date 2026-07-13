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
		$this->assertSame(Status::Ok, Status::from(200));
		$this->assertSame('OK', Status::Ok->getMessage());
		$this->assertSame(200, Status::Ok->getCode());
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

	/**
	 *
	 */
	public function testIsInformational(): void
	{
		$this->assertTrue(Status::Continue->isInformational());
		$this->assertFalse(Status::Continue->isSuccess());
		$this->assertFalse(Status::Continue->isRedirect());
		$this->assertFalse(Status::Continue->isClientError());
		$this->assertFalse(Status::Continue->isServerError());
	}

	/**
	 *
	 */
	public function testIsSuccess(): void
	{
		$this->assertFalse(Status::Ok->isInformational());
		$this->assertTrue(Status::Ok->isSuccess());
		$this->assertFalse(Status::Ok->isRedirect());
		$this->assertFalse(Status::Ok->isClientError());
		$this->assertFalse(Status::Ok->isServerError());
	}

	/**
	 *
	 */
	public function testIsRedirect(): void
	{
		$this->assertFalse(Status::MultipleChoices->isInformational());
		$this->assertFalse(Status::MultipleChoices->isSuccess());
		$this->assertTrue(Status::MultipleChoices->isRedirect());
		$this->assertFalse(Status::MultipleChoices->isClientError());
		$this->assertFalse(Status::MultipleChoices->isServerError());
	}

	/**
	 *
	 */
	public function testIsClientError(): void
	{
		$this->assertFalse(Status::BadRequest->isInformational());
		$this->assertFalse(Status::BadRequest->isSuccess());
		$this->assertFalse(Status::BadRequest->isRedirect());
		$this->assertTrue(Status::BadRequest->isClientError());
		$this->assertFalse(Status::BadRequest->isServerError());
	}

	/**
	 *
	 */
	public function testIsServerError(): void
	{
		$this->assertFalse(Status::InternalServerError->isInformational());
		$this->assertFalse(Status::InternalServerError->isSuccess());
		$this->assertFalse(Status::InternalServerError->isRedirect());
		$this->assertFalse(Status::InternalServerError->isClientError());
		$this->assertTrue(Status::InternalServerError->isServerError());
	}
}
