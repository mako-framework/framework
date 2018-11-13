<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file;

use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\WithParametersTrait;
use mako\validator\rules\WithParametersInterface;

use function sprintf;

/**
 * Hmac rule.
 *
 * @author Frederic G. Østby
 */
class Hmac extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['hmac', 'key', 'algorithm'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$algorithm = $this->getParameter('algorithm', true) ?? 'sha256';

		return $value->validateHmac($this->getParameter('hmac'), $this->getParameter('key'), $algorithm);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not match the expected hmac.', $field);
	}
}
