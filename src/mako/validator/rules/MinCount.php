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
 * Min count rule.
 */
class MinCount extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $minCount
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['minCount'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return is_countable($value) && count($value) >= $this->minCount;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must contain at least %2$s items.', $field, $this->minCount);
	}
}
