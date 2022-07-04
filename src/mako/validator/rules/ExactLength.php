<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function mb_strlen;
use function sprintf;

/**
 * Exact length rule.
 */
class ExactLength extends Rule implements RuleInterface
{
	/**
	 * Length.
	 *
	 * @var int
	 */
	protected $length;

	/**
	 * Constructor.
	 *
	 * @param int $length Length
	 */
	public function __construct(int $length)
	{
		$this->length = $length;
	}

	/**
	 * I18 parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['length'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		return mb_strlen($value) === $this->length;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be exactly %2$s characters long.', $field, $this->length);
	}
}
