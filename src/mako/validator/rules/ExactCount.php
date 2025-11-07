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
 * Exact count rule.
 */
class ExactCount extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $exactCount
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['exactCount'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return is_countable($value) && count($value) === $this->exactCount;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must contain exactly %2$s items.', $field, $this->exactCount);
	}
}
