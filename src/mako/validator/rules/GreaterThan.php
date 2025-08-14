<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function sprintf;

/**
 * Greater than rule.
 */
class GreaterThan extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $greaterThan
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['greaterThan'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value > $this->greaterThan;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than %2$s.', $field, $this->greaterThan);
	}
}
