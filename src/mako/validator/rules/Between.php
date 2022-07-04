<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function sprintf;

/**
 * Between rule.
 */
class Between extends Rule implements RuleInterface
{
	/**
	 * Minimum value.
	 *
	 * @var mixed
	 */
	protected $minimum;

	/**
	 * Maximum value.
	 *
	 * @var mixed
	 */
	protected $maximum;

	/**
	 * Constructor.
	 *
	 * @param mixed $minimum Minimum value
	 * @param mixed $maximum Maximum value
	 */
	public function __construct(mixed $minimum, mixed $maximum)
	{
		$this->minimum = $minimum;

		$this->maximum = $maximum;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['minimum', 'maximum'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value >= $this->minimum && $value <= $this->maximum;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be between %2$s and %3$s.', $field, $this->minimum, $this->maximum);
	}
}
