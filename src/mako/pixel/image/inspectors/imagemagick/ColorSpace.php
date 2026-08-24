<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\inspectors\imagemagick;

use Imagick;
use mako\pixel\image\ColorSpace as ColorSpaceEnum;
use mako\pixel\image\inspectors\InspectorInterface;
use Override;
use ReflectionClass;

use function str_replace;
use function str_starts_with;
use function strtolower;

/**
 * Returns the color space of the image.
 *
 * @implements InspectorInterface<ColorSpaceEnum>
 */
class ColorSpace implements InspectorInterface
{
	/**
	 * Returns a map of the available color spaces.
	 */
	protected static function getColorSpaceMap(): array
	{
		static $map = null;

		if ($map === null) {
			$map = [];

			foreach ((new ReflectionClass(Imagick::class))->getConstants() as $name => $value) {
				if (!str_starts_with($name, 'COLORSPACE_')) {
					continue;
				}

				$map[$value] = strtolower(str_replace('COLORSPACE_', '', $name));
			}
		}

		return $map;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @param Imagick &$imageResource
	 */
	#[Override]
	public function inspect(object &$imageResource): mixed
	{
		$value = static::getColorSpaceMap()[$imageResource->getImageColorspace()] ?? null;

		if ($value === null) {
			return ColorSpaceEnum::Undefined;
		}

		return ColorSpaceEnum::tryFrom($value) ?? ColorSpaceEnum::Undefined;
	}
}
