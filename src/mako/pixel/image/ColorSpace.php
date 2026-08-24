<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

/**
 * Color space.
 */
enum ColorSpace: string
{
	case Cmy = 'cmy';
	case Cmyk = 'cmyk';
	case Gray = 'gray';
	case Hsb = 'hsb';
	case Hsl = 'hsl';
	case Hwb = 'hwb';
	case Lab = 'lab';
	case Log = 'log';
	case Luv = 'luv';
	case Ohta = 'ohta';
	case Rgb = 'rgb';
	case Srgb = 'srgb';
	case Undefined = 'undefined';
	case Xyz = 'xyz';
	case Ycbcr = 'ycbcr';
	case Ycc = 'ycc';
	case Yiq = 'yiq';
	case Ypbpr = 'ypbpr';
	case Yuv = 'yuv';
}
