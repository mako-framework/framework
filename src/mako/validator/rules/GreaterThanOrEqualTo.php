<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Greater than or equal to rule.
 */
class GreaterThanOrEqualTo extends Rule implements RuleInterface
{
	/**
	 * Greater than or equal to.
	 *
	 * @var mixed
	 */
	protected $greaterThanOrEqualTo;

	/**
	 * Constructor.
	 *
	 * @param mixed $greaterThanOrEqualTo Greater than or equal to
	 */
	public function __construct($greaterThanOrEqualTo)
	{
		$this->greaterThanOrEqualTo = $greaterThanOrEqualTo;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['greaterThanOrEqualTo'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return (int) $value >= $this->greaterThanOrEqualTo;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be greater than or equal to %2$s.', $field, $this->greaterThanOrEqualTo);
	}
}
