<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\file\image;

use mako\validator\rules\file\image\traits\GetImageSizeTrait;
use mako\validator\rules\Rule;
use Override;

use function sprintf;

/**
 * Aspect ratio rule.
 */
class AspectRatio extends Rule
{
	use GetImageSizeTrait;

	/**
	 * Constructor.
	 */
	public function __construct(
		protected int $width,
		protected int $height
	) {
	}

	/**
	 * I18n parameters.
	 */
	protected array $i18nParameters = ['width', 'height'];

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function validate(mixed $value, string $field, array $input): bool
	{
		[$width, $height] = $this->getImageSize($value);

		return ($this->width / $this->height) == ($width / $height);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getErrorMessage(string $field): string
	{
		return sprintf('The %1$s does not have the required aspect ratio of %2$s:%3$s.', $field, $this->width, $this->height);
	}
}
