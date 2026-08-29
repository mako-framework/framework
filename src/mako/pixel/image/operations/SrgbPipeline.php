<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image\operations;

/**
 * Temporarily converts the image to sRGB while applying the pipelined operations,
 * then restores the original color space.
 */
abstract class SrgbPipeline extends Pipeline
{

}
