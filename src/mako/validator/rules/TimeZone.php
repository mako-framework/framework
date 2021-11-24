<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTimeZone;

use function in_array;
use function sprintf;

/**
 * Time zone rule.
 */
class TimeZone extends Rule implements RuleInterface
{
	/**
	 * Time zone group.
	 *
	 * @var int
	 */
	protected $group;

	/**
	 * Country code.
	 *
	 * @var string|null
	 */
	protected $country;

	/**
	 * Constructor.
	 *
	 * @param int         $group   Time zone group
	 * @param string|null $country Country code
	 */
	public function __construct(int $group = DateTimeZone::ALL, ?string $country = null)
	{
		$this->group = $group;

		$this->country = $country;
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, string $field, array $input): bool
	{
		return in_array($value, DateTimeZone::listIdentifiers($this->group, $this->country));
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid time zone.', $field);
	}
}
