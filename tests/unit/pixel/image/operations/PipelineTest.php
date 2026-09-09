<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\pixel\image\operations;

use mako\pixel\image\operations\OperationInterface;
use mako\pixel\image\operations\Pipeline;
use mako\tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class PipelineTest extends TestCase
{
	/**
	 *
	 */
	public function testAppend(): void
	{
		$pipeline = new Pipeline;

		$operation = Mockery::mock(OperationInterface::class);

		$this->assertCount(0, $pipeline);

		$this->assertSame($pipeline, $pipeline->append($operation, $operation));

		$this->assertCount(2, $pipeline);
	}
}
