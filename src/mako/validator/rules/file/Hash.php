<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function sprintf;

/**
 * Hash rule.
 */
class Hash extends Rule implements RuleInterface
{
	/**
	 * Hash.
	 *
	 * @var string
	 */
	protected $hash;

	/**
	 * Algorithm.
	 *
	 * @var string
	 */
	protected $algorithm;

	/**
	 * Constructor.
	 *
	 * @param string $hash      Hash
	 * @param string $algorithm Algorithm
	 */
	public function __construct(string $hash, string $algorithm = 'sha256')
	{
		$this->hash = $hash;

		$this->algorithm = $algorithm;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['hash', 'algorithm'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		return $value->validateHash($this->hash, $this->algorithm);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hash.', $field);
	}
}
