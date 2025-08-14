<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function sprintf;

/**
 * Less than or equal to rule.
 */
class LessThanOrEqualTo extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $lessThanOrEqualTo
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['lessThanOrEqualTo'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value <= $this->lessThanOrEqualTo;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than or equal to %2$s.', $field, $this->lessThanOrEqualTo);
	}
}
