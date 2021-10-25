<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function mb_strlen;
use function sprintf;

/**
 * Max length rule.
 *
 * @author Frederic G. Østby
 */
class MaxLength extends Rule implements RuleInterface
{
	/**
	 * Max length.
	 *
	 * @var int
	 */
	protected $maxLength;

	/**
	 * Constructor.
	 *
	 * @param int $maxLength Max length
	 */
	public function __construct(int $maxLength)
	{
		$this->maxLength = $maxLength;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['maxLength'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) <= $this->maxLength;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at most %2$s characters long.', $field, $this->maxLength);
	}
}
