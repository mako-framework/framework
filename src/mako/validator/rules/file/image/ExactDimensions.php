<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file\image;

use mako\validator\rules\file\image\traits\GetImageSizeTrait;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\WithParametersTrait;
use mako\validator\rules\WithParametersInterface;

use function sprintf;

/**
 * Exact dimensions rule.
 *
 * @author Frederic G. Østby
 */
class ExactDimensions extends Rule implements RuleInterface, WithParametersInterface
{
	use GetImageSizeTrait;
	use WithParametersTrait;

	/**
	 * Parameters.
	 *
	 * @var array
	 */
	protected $parameters = ['width', 'height'];

	/**
	 * {@inheritdoc}
	 */
	public function validate($value, array $input): bool
	{
		list($width, $height) = $this->getImageSize($value);

		return $this->getParameter('width') == $width && $this->getParameter('height') == $height;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not meet the required dimensions of %2$sx%3$s pixels.', $field, $this->getParameter('width'), $this->getParameter('height'));
	}
}
