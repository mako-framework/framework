<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function mb_strlen;
use function sprintf;

/**
 * Min length rule.
 *
 * @author Frederic G. Østby
 */
class MinLength extends Rule implements RuleInterface
{
	/**
	 * Min length.
	 *
	 * @var int
	 */
	protected $minLength;

	/**
	 * Constructor.
	 *
	 * @param int $minLength Min length
	 */
	public function __construct(int $minLength)
	{
		$this->minLength = $minLength;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['minLength'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return mb_strlen($value) >= $this->minLength;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field must be at least %2$s characters long.', $field, $this->minLength);
	}
}
