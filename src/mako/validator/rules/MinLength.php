<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function mb_strlen;
use function sprintf;

/**
 * Min length rule.
 */
class MinLength extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $minLength
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['minLength'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return mb_strlen($value) >= $this->minLength;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at least %2$s characters long.', $field, $this->minLength);
	}
}
