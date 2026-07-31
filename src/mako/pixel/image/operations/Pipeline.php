<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Override;

/**
 * Operation pipeline.
 */
final class Pipeline implements OperationInterface
{
	/**
	 * @var array<OperationInterface>
	 */
	private array $operations;

	/**
	 * Constructor.
	 */
	public function __construct(OperationInterface ...$operation)
	{
		$this->operations = $operation;
	}

	#[Override]
	public function apply(object &$imageResource): void
	{
		foreach ($this->operations as $operation) {
			$operation->apply($imageResource);
		}
	}
}
