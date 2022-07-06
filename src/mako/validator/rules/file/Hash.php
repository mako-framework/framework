<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function sprintf;

/**
 * Hash rule.
 */
class Hash extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 *
	 * @param string $hash      Hash
	 * @param string $algorithm Algorithm
	 */
	public function __construct(
		protected string $hash,
		protected string $algorithm = 'sha256'
	)
	{}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['hash', 'algorithm'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value->validateHash($this->hash, $this->algorithm);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hash.', $field);
	}
}
