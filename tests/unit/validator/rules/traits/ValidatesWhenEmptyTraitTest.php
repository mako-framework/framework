<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\traits;

use mako\tests\TestCase;
use mako\validator\rules\traits\ValidatesWhenEmptyTrait;

/**
 * @group unit
 */
class ValidatesWhenEmptyTraitTest extends TestCase
{
	/**
	 *
	 */
	public function test(): void
	{
		$rule = new class
		{
			use ValidatesWhenEmptyTrait;
		};

		$this->assertTrue($rule->validateWhenEmpty());
	}
}
