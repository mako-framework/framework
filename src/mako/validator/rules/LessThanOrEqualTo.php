<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Less than or equal to rule.
 *
 * @author Frederic G. Østby
 */
class LessThanOrEqualTo extends Rule implements RuleInterface
{
	/**
	 * Less than or equal to.
	 *
	 * @var mixed
	 */
	protected $lessThanOrEqualTo;

	/**
	 * Constructor.
	 *
	 * @param mixed $lessThanOrEqualTo Less than or equal to
	 */
	public function __construct($lessThanOrEqualTo)
	{
		$this->lessThanOrEqualTo = $lessThanOrEqualTo;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['lessThanOrEqualTo'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value <= $this->lessThanOrEqualTo;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be less than or equal to %2$s.', $field, $this->lessThanOrEqualTo);
	}
}
