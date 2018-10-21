<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Greater than or equal to rule.
 *
 * @author Frederic G. Østby
 */
class GreaterThanOrEqualTo extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['greaterThanOrEqualTo'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value >= $this->getParameter('greaterThanOrEqualTo');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than or equal to %2$s.', $field, $this->parameters['greaterThanOrEqualTo']);
	}
}
