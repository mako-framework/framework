<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

/**
 * Less than or equal to rule.
 *
 * @author Frederic G. Østby
 */
class LessThanOrEqualTo extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['lessThanOrEqualTo'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value <= $this->getParameter('lessThanOrEqualTo');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than or equal to %2$s.', $field, $this->parameters['lessThanOrEqualTo']);
	}
}
