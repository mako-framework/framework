<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\rules\traits\WithParametersTrait;

use function in_array;
use function sprintf;

/**
 * In rule.
 *
 * @author Frederic G. Østby
 */
class In extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['values'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return in_array($value, $this->getParameter('values'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain one of available options.', $field);
	}
}
