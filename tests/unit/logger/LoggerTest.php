<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\logger;

use mako\logger\Logger;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;

#[Group('unit')]
class LoggerTest extends TestCase
{
	/**
	 *
	 */
	public function testGetAndSetContext(): void
	{
		$logger = new Logger(Mockery::mock(LoggerInterface::class));

		$this->assertSame([], $logger->getContext());

		$context = ['foo' => 'bar'];

		$logger->setContext($context);

		$this->assertSame($context, $logger->getContext());
	}
}
