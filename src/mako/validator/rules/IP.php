<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\validator\exceptions\ValidatorException;

use function filter_var;
use function sprintf;
use function vsprintf;

/**
 * IP rule.
 */
class IP extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 *
	 * @param string|null $version IP version
	 */
	public function __construct(
		protected ?string $version = null
	)
	{}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['version'];

	/**
	 * Returns the filter flags.
	 *
	 * @return int
	 */
	protected function getFlags(): int
	{
		return match($this->version)
		{
			'v4'    => FILTER_FLAG_IPV4,
			'v6'    => FILTER_FLAG_IPV6,
			null    => 0,
			default => throw new ValidatorException(vsprintf('Invalid IP version [ %s ]. The accepted versions are v4 and v6.', [$this->version])),
		};
	}

	/**
	 * Returns the name of the IP version that we're validating.
	 *
	 * @return string
	 */
	protected function getVersion(): string
	{
		return match($this->version)
		{
			'v4'    => 'IPv4',
			'v6'    => 'IPv6',
			default => 'IP',
		};
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return filter_var($value, FILTER_VALIDATE_IP, $this->getFlags()) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s field must contain a valid %2$s address.', $field, $this->getVersion());
	}
}
