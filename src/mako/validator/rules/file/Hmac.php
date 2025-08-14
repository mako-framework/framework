<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use Override;
use SensitiveParameter;

use function sprintf;

/**
 * Hmac rule.
 */
class Hmac extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $hmac,
		#[SensitiveParameter] protected string $key,
		protected string $algorithm = 'sha256'
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['hmac', 'algorithm'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value->validateHmac($this->hmac, $this->key, $this->algorithm);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hmac.', $field);
	}
}
