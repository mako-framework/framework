<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTime;
use mako\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Before rule.
 *
 * @author Frederic G. Østby
 */
class Before extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['format', 'date'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$date = DateTime::createFromFormat(($format = $this->getParameter('format')), $value);

		if($date === false || $date->format($format) !== $value)
		{
			return false;
		}

		return ($date->getTimestamp() < DateTime::createFromFormat($format, $this->getParameter('date'))->getTimestamp());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid date before %2$s.', $field, $this->parameters['date']);
	}
}
