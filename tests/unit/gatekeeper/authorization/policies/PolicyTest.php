<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\gatekeeper\authorization\policies;

use mako\gatekeeper\authorization\policies\Policy;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PolicyTest extends TestCase
{
	/**
	 *
	 */
	public function testThatBeforeReturnsNull(): void
	{
		$policy = new class extends Policy {

		};

		$this->assertNull($policy->before(null, 'update', 'entity'));
	}
}
