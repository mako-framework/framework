<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Base test case.
 *
 * @author Frederic G. Østby
 */
abstract class TestCase extends PHPUnitTestCase
{
	use MockeryPHPUnitIntegration;
}
