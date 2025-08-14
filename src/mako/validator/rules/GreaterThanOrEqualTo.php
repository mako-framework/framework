<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function sprintf;

/**
 * Greater than or equal to rule.
 */
class GreaterThanOrEqualTo extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $greaterThanOrEqualTo
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['greaterThanOrEqualTo'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value >= $this->greaterThanOrEqualTo;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than or equal to %2$s.', $field, $this->greaterThanOrEqualTo);
	}
}
