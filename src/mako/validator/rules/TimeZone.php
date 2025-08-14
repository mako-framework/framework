<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use DateTimeZone;
use Override;

use function in_array;
use function sprintf;

/**
 * Time zone rule.
 */
class TimeZone extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $group = DateTimeZone::ALL,
		protected ?string $country = null
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return in_array($value, DateTimeZone::listIdentifiers($this->group, $this->country));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid time zone.', $field);
	}
}
