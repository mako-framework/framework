<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\logger;

use mako\logger\Logger;
use mako\tests\TestCase;

/**
 * @group unit
 */
class LoggerTest extends TestCase
{
	/**
	 *
	 */
	public function testGetAndSetContext(): void
	{
		$logger = new Logger('test');

		$this->assertSame([], $logger->getContext());

		$context = ['foo' => 'bar'];

		$logger->setContext($context);

		$this->assertSame($context, $logger->getContext());
	}
}
