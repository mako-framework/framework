<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

/**
 * Less than rule.
 *
 * @author Frederic G. Østby
 */
class LessThan extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['lessThan'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value < $this->getParameter('lessThan');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than %2$s.', $field, $this->parameters['lessThan']);
	}
}
