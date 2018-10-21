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
 * Date rule.
 *
 * @author Frederic G. Østby
 */
class Date extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['format'];

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

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid date.', $field);
	}
}
