<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function method_exists;
use function sprintf;

/**
 * Enum rule.
 */
class Enum extends Rule implements RuleInterface
{
	/**
	 * Enum.
	 *
	 * @var string
	 */
	protected $enum;

	/**
	 * Constructor.
	 *
	 * @param string $enum Enum class
	 */
	public function __construct(string $enum)
	{
		$this->enum = $enum;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		if(method_exists($this->enum, 'tryFrom'))
		{
			return ($this->enum)::tryFrom($value) !== null;
		}

		foreach(($this->enum)::cases() as $case)
		{
			if($case->name === $value)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid enum value.', $field);
	}
}
