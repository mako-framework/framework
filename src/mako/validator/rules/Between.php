<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function sprintf;

/**
 * Between rule.
 */
class Between extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected mixed $minimum,
		protected mixed $maximum
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['minimum', 'maximum'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value >= $this->minimum && $value <= $this->maximum;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be between %2$s and %3$s.', $field, $this->minimum, $this->maximum);
	}
}
