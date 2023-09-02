<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file\image;

use mako\validator\rules\file\image\traits\GetImageSizeTrait;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;

use function sprintf;

/**
 * Exact dimensions rule.
 */
class ExactDimensions extends Rule implements RuleInterface
{
	use GetImageSizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $width,
		protected int $height
	)
	{}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['width', 'height'];

	/**
	 * {@inheritDoc}
	 */
	public function validate(mixed $value, string $field, array $input): bool
	{
		[$width, $height] = $this->getImageSize($value);

		return $this->width == $width && $this->height == $height;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not meet the required dimensions of %2$sx%3$s pixels.', $field, $this->width, $this->height);
	}
}
