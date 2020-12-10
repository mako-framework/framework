<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Less than rule.
 */
class LessThan extends Rule implements RuleInterface
{
	/**
	 * Less than.
	 *
	 * @var mixed
	 */
	protected $lessThan;

	/**
	 * Constructor.
	 *
	 * @param mixed $lessThan Less than
	 */
	public function __construct($lessThan)
	{
		$this->lessThan = $lessThan;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['lessThan'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value < $this->lessThan;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than %2$s.', $field, $this->lessThan);
	}
}
