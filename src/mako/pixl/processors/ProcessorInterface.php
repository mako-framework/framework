<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\pixl\processors;

use mako\pixl\Image;

/**
 * Image manipulation processor interface.
 *
 * @author  Frederic G. Østby
 */

interface ProcessorInterface
{
	/**
	 * Opens the image we want to work with.
	 *
	 * @access  public
	 * @param   string  $image  Path to image file
	 */

	public function open($image);

	/**
	 * Creates a snapshot of the image resource.
	 *
	 * @access  public
	 */

	public function snapshot();

	/**
	 * Restores an image snapshot.
	 *
	 * @access  public
	 */

	public function restore();

	/**
	 * Returns the image width in pixels.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getWidth();

	/**
	 * Returns the image height in pixels.
	 *
	 * @access  public
	 * @return  int
	 */

	public function getHeight();

	/**
	 * Returns an array containing the image dimensions in pixels.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getDimensions();

	/**
	 * Rotates the image using the given angle in degrees.
	 *
	 * @access  public
	 * @param   int     $degrees  Degrees to rotate the image
	 */

	public function rotate($degrees);

	/**
	 * Resizes the image to the chosen size.
	 *
	 * @access  public
	 * @param  int      $width        Width of the image
	 * @param  int      $height       Height of the image
	 * @param  int      $aspectRatio  Aspect ratio
	 */

	public function resize($width, $height = null, $aspectRatio = Image::RESIZE_IGNORE);

	/**
	 * Crops the image.
	 *
	 * @access  public
	 * @param   int  $width   Width of the crop
	 * @param   int  $height  Height of the crop
	 * @param   int  $x       The X coordinate of the cropped region's top left corner
	 * @param   int  $y       The Y coordinate of the cropped region's top left corner
	 */

	public function crop($width, $height, $x, $y);

	/**
	 * Flips the image.
	 *
	 * @access  public
	 * @param   int     $direction  Direction to flip the image
	 */

	public function flip($direction = Image::FLIP_HORIZONTAL);

	/**
	 * Adds a watermark to the image.
	 *
	 * @access  public
	 * @param   string  $file      Path to the image file
	 * @param   int     $position  Position of the watermark
	 * @param   int     $opacity   Opacity of the watermark in percent
	 */

	public function watermark($file, $position = Image::WATERMARK_TOP_LEFT, $opacity = 100);

	/**
	 * Adjust image brightness.
	 *
	 * @access  public
	 * @param   int     $level  Brightness level (-100 to 100)
	 */

	public function brightness($level = 50);

	/**
	 * Converts image to greyscale.
	 *
	 * @access  public
	 */

	public function greyscale();

	/**
	 * Converts image to sepia.
	 *
	 * @access  public
	 */

	public function sepia();

	/**
	 * Colorize the image.
	 *
	 * @access  public
	 * @param   string  $color  Hex value
	 */

	public function colorize($color);

	/**
	 * Sharpens the image.
	 *
	 * @access  public
	 */

	public function sharpen();

	/**
	 * Pixelates the image.
	 *
	 * @access  public
	 * @param   int     $pixelSize  Pixel size
	 */

	public function pixelate($pixelSize = 10);

	/**
	 * Negates the image.
	 *
	 * @access  public
	 */

	public function negate();

	/**
	 * Adds a border to the image.
	 *
	 * @access  public
	 * @param   string  $color      Hex code for the color
	 * @param   int     $thickness  Thickness of the frame in pixels
	 */

	public function border($color = '#000', $thickness = 5);

	/**
	 * Returns a string containing the image.
	 *
	 * @access  public
	 * @param   string  $type     Image type
	 * @param   int     $quality  Image quality 1-100
	 * @return  string
	 */

	public function getImageBlob($type = null, $quality = 95);

	/**
	 * Saves image to file.
	 *
	 * @access  public
	 * @param   string  $file     Path to the image file
	 * @param   int     $quality  Image quality 1-100
	 */

	public function save($file, $quality = 95);
}