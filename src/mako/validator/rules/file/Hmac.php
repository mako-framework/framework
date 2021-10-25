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
 * Hmac rule.
 *
 * @author Frederic G. Østby
 */
class Hmac extends Rule implements RuleInterface
{
	/**
	 * HMAC.
	 *
	 * @var string
	 */
	protected $hmac;

	/**
	 * Key.
	 *
	 * @var string
	 */
	protected $key;

	/**
	 * Algorithm.
	 *
	 * @var string
	 */
	protected $algorithm;

	/**
	 * Constructor.
	 *
	 * @param string $hmac      Hmac
	 * @param string $key       Key
	 * @param string $algorithm Algorithm
	 */
	public function __construct(string $hmac, string $key, string $algorithm = 'sha256')
	{
		$this->hmac = $hmac;

		$this->key = $key;

		$this->algorithm = $algorithm;
	}

	/**
	 * I18n parameters.
	 *
	 * @var array
	 */
	protected $i18nParameters = ['hmac', 'algorithm'];

	/**
	 * {@inheritDoc}
	 */
	public function validate($value, array $input): bool
	{
		return $value->validateHmac($this->hmac, $this->key, $this->algorithm);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hmac.', $field);
	}
}
