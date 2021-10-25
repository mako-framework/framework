<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function in_array;
use function sprintf;

/**
 * In rule.
 *
 * @author Frederic G. Østby
 */
class In extends Rule implements RuleInterface
{
	/**
	 * Allowed values.
	 *
	 * @var array
	 */
	protected $values;

	/**
	 * Constructor.
	 *
	 * @param array $values Allowed values
	 */
	public function __construct(array $values)
	{
		$this->values = $values;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['values'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return in_array($value, $this->values);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain one of available options.', $field);
	}
}
