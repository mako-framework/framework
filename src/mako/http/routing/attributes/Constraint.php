<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\attributes;

use Attribute;
use mako\http\routing\constraints\ConstraintInterface;

/**
 * Constraint attribute.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Constraint
{
	/**
	 * Parameters.
	 */
	protected array $parameters;

	/**
	 * Constructor.
	 *
	 * @param class-string<ConstraintInterface> $constraint
	 */
	public function __construct(
		protected string $constraint,
		mixed ...$parameters
	) {
		$this->parameters = $parameters;
	}

	/**
	 * Returns the constraint.
	 */
	public function getConstraint(): string
	{
		return $this->constraint;
	}

	/**
	 * Returns the constraint parameters.
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns the constraint and parameters.
	 *
	 * @return array{constraint: string, parameters: array}
	 */
	public function getConstraintAndParameters(): array
	{
		return ['constraint' => $this->constraint, 'parameters' => $this->parameters];
	}
}
