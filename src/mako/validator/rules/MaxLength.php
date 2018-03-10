<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

/**
 * Max length rule.
 *
 * @author Frederic G. Østby
 */
class MaxLength extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['maxLength'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) <= $this->getParameter('maxLength');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at most %2$s characters long.', $field, $this->parameters['maxLength']);
	}
}
