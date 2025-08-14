<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use mako\utility\Arr;
use Override;

use function sprintf;

/**
 * Match field rule.
 */
class MatchField extends Rule implements RuleInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		protected string $field
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['field'];

	/**
	 * Parameters holding additional i18n field names.
	 */
	protected array $i18nFieldNameParameters = ['field'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		return Arr::has($input, $this->field) && $value === Arr::get($input, $this->field);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The values of the %1$s field and %2$s field must match.', $field, $this->field);
	}
}
