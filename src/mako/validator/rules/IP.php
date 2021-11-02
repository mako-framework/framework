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
	 * IP version.
	 *
	 * @var string|null
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @param string|null $version IP version
	 */
	public function __construct(?string $version = null)
	{
		$this->version = $version;
	}

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
		if($this->version === null)
		{
			return 0;
		}

		switch($this->version)
		{
			case 'v4':
				return FILTER_FLAG_IPV4;
			case 'v6':
				return FILTER_FLAG_IPV6;
			default:
				throw new ValidatorException(vsprintf('Invalid IP version [ %s ]. The accepted versions are v4 and v6.', [$this->version]));
		}
	}

	/**
	 * Returns the name of the IP version that we're validating.
	 *
	 * @return string
	 */
	protected function getVersion(): string
	{
		switch($this->version)
		{
			case 'v4':
				return 'IPv4';
			case 'v6':
				return 'IPv6';
			default:
				return 'IP';
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
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
