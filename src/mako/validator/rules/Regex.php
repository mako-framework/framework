<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use Override;

use function preg_match;
use function sprintf;

/**
 * Regex rule.
 */
class Regex extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $regex
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['regex'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return preg_match($this->regex, $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field does not match the required format.', $field);
	}
}
