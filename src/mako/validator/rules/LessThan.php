<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function sprintf;

/**
 * Less than rule.
 */
class LessThan extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $lessThan
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['lessThan'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value < $this->lessThan;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than %2$s.', $field, $this->lessThan);
	}
}
