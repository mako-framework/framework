<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

use Countable;
use Override;

use function count;

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
	 * Appends one or more operations to the pipeline.
	 *
	 * @return $this
	 */
	public function append(OperationInterface ...$operation): static
	{
		$this->operations = [...$this->operations, ...$operation];

		return $this;
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
