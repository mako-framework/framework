<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use \mako\pixl\Image;

/**
 * Image manipulation processor interface.
 *
 * @author  Frederic G. Østby
 */

interface ProcessorInterface
{
	public function open($image);

	public function rotate($degrees);

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE);

	public function crop($width, $height, $x, $y);

	public function flip($direction = Image::FLIP_HORIZONTAL);

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100);

	public function greyscale();

	public function sepia();

	public function colorize($color);

	public function pixelate($pixelSize = 10);

	public function border($color = '#000', $thickness = 5);

	public function getImageBlob($type = null, $quality = 95);

	public function save($file, $quality = 95);
}