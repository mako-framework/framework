<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

use function sprintf;

/**
 * Between rule.
 *
 * @author Frederic G. Østby
 */
class Between extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['minimum', 'maximum'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value >= $this->getParameter('minimum') && (int) $value <= $this->getParameter('maximum');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be between %2$s and %3$s.', $field, $this->parameters['minimum'], $this->parameters['maximum']);
	}
}
