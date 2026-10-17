<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\metadata\xmp;

/**
 * Well-known XMP namespaces.
 */
final class XmpNamespace
{
	public const string CC = 'http://creativecommons.org/ns#';
	public const string CRS = 'http://ns.adobe.com/camera-raw-settings/1.0/';
	public const string DC = 'http://purl.org/dc/elements/1.1/';
	public const string DC_TERMS = 'http://purl.org/dc/terms/';
	public const string EXIF = 'http://ns.adobe.com/exif/1.0/';
	public const string EXIF_AUX = 'http://ns.adobe.com/exif/1.0/aux/';
	public const string EXIF_EX = 'http://cipa.jp/exif/1.0/';
	public const string GPANO = 'http://ns.google.com/photos/1.0/panorama/';
	public const string IPTC_CORE = 'http://iptc.org/std/Iptc4xmpCore/1.0/xmlns/';
	public const string IPTC_EXT = 'http://iptc.org/std/Iptc4xmpExt/2008-02-29/';
	public const string LIGHTROOM = 'http://ns.adobe.com/lightroom/1.0/';
	public const string MICROSOFT_PHOTO = 'http://ns.microsoft.com/photo/1.0/';
	public const string PDF = 'http://ns.adobe.com/pdf/1.3/';
	public const string PHOTOSHOP = 'http://ns.adobe.com/photoshop/1.0/';
	public const string PLUS = 'http://ns.useplus.org/ldf/xmp/1.0/';
	public const string TIFF = 'http://ns.adobe.com/tiff/1.0/';
	public const string XMP = 'http://ns.adobe.com/xap/1.0/';
	public const string XMP_MM = 'http://ns.adobe.com/xap/1.0/mm/';
	public const string XMP_RIGHTS = 'http://ns.adobe.com/xap/1.0/rights/';

	/**
	 * Constructor.
	 */
	private function __construct()
	{
	}
}
