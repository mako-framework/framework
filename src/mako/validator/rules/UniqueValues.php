<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function array_unique;
use function count;
use function sprintf;

/**
 * Unique values rule.
 */
class UniqueValues extends Rule implements RuleInterface
{
	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['unique'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return count($value) === count(array_unique($value));
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %s field must only contain unique values.', $field);
	}
}
