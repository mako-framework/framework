<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use Override;

use function sprintf;

/**
 * Hash rule.
 */
class Hash extends Rule
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $hash,
		protected string $algorithm = 'sha256'
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['hash', 'algorithm'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return $value->validateHash($this->hash, $this->algorithm);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hash.', $field);
	}
}
