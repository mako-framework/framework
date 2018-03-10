<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\utility\Arr;
use mako\validator\rules\traits\WithParametersTrait;

/**
 * Match rule.
 *
 * @author Frederic G. Østby
 */
class Match extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['field'];

	/**
	 * Parameters holding additional i18n field names.
	 *
	 * @var array
	 */
	protected $i18nFieldNameParameters = ['field'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return Arr::has($input, $this->getParameter('field')) && $value === Arr::get($input, $this->getParameter('field'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The values of the %1$s field and %2$s field must match.', $field, $this->parameters['field']);
	}
}
