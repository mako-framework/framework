<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTime;

use function sprintf;

/**
 * Before rule.
 */
class Before extends Rule implements RuleInterface
{
	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['format', 'date'];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $format,
		protected string $date
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		$date = DateTime::createFromFormat($this->format, $value);

		if ($date === false || $date->format($this->format) !== $value) {
			return false;
		}

		return $date < DateTime::createFromFormat($this->format, $this->date);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid date before %2$s.', $field, $this->date);
	}
}
