<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\cli\input\arguments\exceptions;

use mako\cli\input\arguments\exceptions\InvalidArgumentException;
use mako\tests\TestCase;

/**
 * @group unit
 */
class InvalidArgumentExceptionTest extends TestCase
{
	/**
	 *
	 */
	public function testExceptionWithoutSuggestion(): void
	{
		$exception = new InvalidArgumentException('Unknown argument [ foo ].');

		$this->assertEquals('Unknown argument [ foo ].', $exception->getMessage());
	}

	/**
	 *
	 */
	public function testExceptionWithSuggestion(): void
	{
		$exception = new InvalidArgumentException('Unknown argument [ foo ].', 'bar');

		$this->assertEquals('Unknown argument [ foo ]. Did you mean [ bar ]?', $exception->getMessage());
	}
}
