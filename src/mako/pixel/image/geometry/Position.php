<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\geometry;

/**
 * Position calculation helpers.
 *
 * Provides methods for calculating the top-left coordinates required to place
 * objects within a container. The returned positions can be used as offsets
 * when drawing or placing objects on an image.
 */
final class Position
{
	/**
	 * Returns the position required to place an object at the top-left corner
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function topLeft(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point($margin, $margin);
	}

	/**
	 * Returns the position required to place an object at the top center
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function top(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			(int) (($container->width - $object->width) / 2),
			$margin,
		);
	}

	/**
	 * Returns the position required to place an object at the top-right corner
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function topRight(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			$container->width - $object->width - $margin,
			$margin,
		);
	}

	/**
	 * Returns the position required to place an object at the center-left
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function centerLeft(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			$margin,
			(int) (($container->height - $object->height) / 2),
		);
	}

	/**
	 * Returns the position required to center an object inside a container
	 * with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function center(Dimensions $container, Dimensions $object): Point
	{
		return new Point(
			(int) (($container->width - $object->width) / 2),
			(int) (($container->height - $object->height) / 2),
		);
	}

	/**
	 * Returns the position required to place an object at the center-right
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function centerRight(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			$container->width - $object->width - $margin,
			(int) (($container->height - $object->height) / 2),
		);
	}

	/**
	 * Returns the position required to place an object at the bottom-left
	 * corner of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function bottomLeft(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			$margin,
			$container->height - $object->height - $margin,
		);
	}

	/**
	 * Returns the position required to place an object at the bottom center
	 * of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function bottom(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			(int) (($container->width - $object->width) / 2),
			$container->height - $object->height - $margin,
		);
	}

	/**
	 * Returns the position required to place an object at the bottom-right
	 * corner of a container with the given margin from the edges.
	 *
	 * The returned point represents the top-left coordinate where the object
	 * should be placed.
	 */
	public static function bottomRight(Dimensions $container, Dimensions $object, int $margin = 0): Point
	{
		return new Point(
			$container->width - $object->width - $margin,
			$container->height - $object->height - $margin,
		);
	}
}
