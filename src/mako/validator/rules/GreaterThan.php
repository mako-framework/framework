<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Greater than rule.
 */
class GreaterThan extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 *
	 * @param mixed $greaterThan Greater than
	 */
	public function __construct(
		protected mixed $greaterThan
	)
	{}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['greaterThan'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value > $this->greaterThan;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than %2$s.', $field, $this->greaterThan);
	}
}
