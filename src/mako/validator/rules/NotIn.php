<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function in_array;
use function sprintf;

/**
 * Not in rule.
 */
class NotIn extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $values
	)
	{}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['values'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return !in_array($value, $this->values);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field contains an invalid value.', $field);
	}
}
