<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTime;

use function sprintf;

/**
 * After rule.
 *
 * @author Frederic G. Østby
 */
class After extends Rule implements RuleInterface
{
	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected $format;

	/**
	 * Date.
	 *
	 * @var string
	 */
	protected $date;

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['format', 'date'];

	/**
	 * Constructor.
	 *
	 * @param string $format Date format
	 * @param string $date   Date
	 */
	public function __construct(string $format, string $date)
	{
		$this->format = $format;

		$this->date = $date;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$date = DateTime::createFromFormat($this->format, $value);

		if($date === false || $date->format($this->format) !== $value)
		{
			return false;
		}

		return ($date->getTimestamp() > DateTime::createFromFormat($this->format, $this->date)->getTimestamp());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid date after %2$s.', $field, $this->date);
	}
}
