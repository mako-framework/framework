<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function count;
use function is_countable;
use function sprintf;

/**
 * Max count rule.
 */
class MaxCount extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $maxCount
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['maxCount'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return is_countable($value) && count($value) <= $this->maxCount;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must contain at most %2$s items.', $field, $this->maxCount);
	}
}
