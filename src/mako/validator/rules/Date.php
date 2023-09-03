<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTime;

use function sprintf;

/**
 * Date rule.
 */
class Date extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $format
	)
	{}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['format'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		$date = DateTime::createFromFormat($this->format, $value);

		if($date === false || $date->format($this->format) !== $value)
		{
			return false;
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid date.', $field);
	}
}
