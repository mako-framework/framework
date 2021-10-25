<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules;

use function preg_match;
use function sprintf;

/**
 * Regex rule.
 *
 * @author Frederic G. Østby
 */
class Regex extends Rule implements RuleInterface
{
	/**
	 * Regex.
	 *
	 * @var string
	 */
	protected $regex;

	/**
	 * Constructor.
	 *
	 * @param string $regex Regex
	 */
	public function __construct(string $regex)
	{
		$this->regex = $regex;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['regex'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return preg_match($this->regex, $value) === 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The value of the %1$s field does not match the required format.', $field);
	}
}
