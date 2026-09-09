<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Countable;
use Override;

/**
 * Operation pipeline.
 */
class Pipeline implements Countable, OperationInterface
{
	/**
	 * @var array<OperationInterface>
	 */
	protected array $operations;

	/**
	 * Constructor.
	 */
	public function __construct(OperationInterface ...$operation)
	{
		$this->operations = $operation;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function count(): int
	{
		return count($this->operations);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function apply(object &$imageResource): void
	{
		foreach ($this->operations as $operation) {
			$operation->apply($imageResource);
		}
	}
}
