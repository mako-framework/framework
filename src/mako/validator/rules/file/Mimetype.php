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

use function implode;
use function in_array;
use function sprintf;

/**
 * Mimetype rule.
 *
 * @author Frederic G. Østby
 */
class Mimetype extends Rule implements RuleInterface, WithParametersInterface
{
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['mimetype'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		$mimetypes = (array) $this->getParameter('mimetype');

		return in_array($value->getMimeType(), $mimetypes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s must be a file of type: %2$s.', $field, implode(', ', $this->parameters['mimetype']));
	}
}
