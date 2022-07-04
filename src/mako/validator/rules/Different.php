<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\utility\Arr;

use function sprintf;

/**
 * Different rule.
 */
class Different extends Rule implements RuleInterface
{
	/**
	 * Field name.
	 *
	 * @var string
	 */
	protected $field;

	/**
	 * Constructor.
	 *
	 * @param string $field Field name
	 */
	public function __construct(string $field)
	{
		$this->field = $field;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['field'];

	/**
	 * Parameters holding i18n field names.
	 *
	 * @var array
	 */
	protected $i18nFieldNameParameters = ['field'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return Arr::has($input, $this->field) && $value !== Arr::get($input, $this->field);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The values of the %1$s field and %2$s field must be different.', $field, $this->field);
	}
}
