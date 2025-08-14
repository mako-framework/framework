<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function mb_strlen;
use function sprintf;

/**
 * Max length rule.
 */
class MaxLength extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $maxLength
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['maxLength'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return mb_strlen($value) <= $this->maxLength;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at most %2$s characters long.', $field, $this->maxLength);
	}
}
