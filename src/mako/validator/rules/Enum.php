<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\exceptions\ValidatorException;
use Override;

use function enum_exists;
use function method_exists;
use function sprintf;

/**
 * Enum rule.
 */
class Enum extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $enum
	) {
		if (enum_exists($this->enum) === false) {
			throw new ValidatorException(sprintf('[ %s ] is not a valid enum.', $this->enum));
		}
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		if (method_exists($this->enum, 'tryFrom')) {
			return ($this->enum)::tryFrom($value) !== null;
		}

		foreach (($this->enum)::cases() as $case) {
			if ($case->name === $value) {
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid enum value.', $field);
	}
}
