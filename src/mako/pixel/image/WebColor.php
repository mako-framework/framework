<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\pixel\image;

/**
 * Web colors.
 */
enum WebColor: string
{
	// Aliases

	public const Fuchsia = self::Magenta;
	public const Aqua = self::Cyan;

	// Pink

	case MediumVioletRed = '#C71585';
	case DeepPink = '#FF1493';
	case PaleVioletRed = '#DB7093';
	case HotPink = '#FF69B4';
	case LightPink = '#FFB6C1';
	case Pink = '#FFC0CB';

	// Red

	case DarkRed = '#8B0000';
	case Red = '#FF0000';
	case Firebrick = '#B22222';
	case Crimson = '#DC143C';
	case IndianRed = '#CD5C5C';
	case LightCoral = '#F08080';
	case Salmon = '#FA8072';
	case DarkSalmon = '#E9967A';
	case LightSalmon = '#FFA07A';

	// Orange

	case OrangeRed = '#FF4500';
	case Tomato = '#FF6347';
	case DarkOrange = '#FF8C00';
	case Coral = '#FF7F50';
	case Orange = '#FFA500';

	// Yellow

	case DarkKhaki = '#BDB76B';
	case Gold = '#FFD700';
	case Khaki = '#F0E68C';
	case PeachPuff = '#FFDAB9';
	case Yellow = '#FFFF00';
	case PaleGoldenrod = '#EEE8AA';
	case Moccasin = '#FFE4B5';
	case PapayaWhip = '#FFEFD5';
	case LightGoldenrodYellow = '#FAFAD2';
	case LemonChiffon = '#FFFACD';
	case LightYellow = '#FFFFE0';

	// Brown

	case Maroon = '#800000';
	case Brown = '#A52A2A';
	case SaddleBrown = '#8B4513';
	case Sienna = '#A0522D';
	case Chocolate = '#D2691E';
	case DarkGoldenrod = '#B8860B';
	case Peru = '#CD853F';
	case RosyBrown = '#BC8F8F';
	case Goldenrod = '#DAA520';
	case SandyBrown = '#F4A460';
	case Tan = '#D2B48C';
	case Burlywood = '#DEB887';
	case Wheat = '#F5DEB3';
	case NavajoWhite = '#FFDEAD';
	case Bisque = '#FFE4C4';
	case BlanchedAlmond = '#FFEBCD';
	case Cornsilk = '#FFF8DC';

	// Purple, violet, and magenta

	case Indigo = '#4B0082';
	case Purple = '#800080';
	case DarkMagenta = '#8B008B';
	case DarkViolet = '#9400D3';
	case DarkSlateBlue = '#483D8B';
	case BlueViolet = '#8A2BE2';
	case DarkOrchid = '#9932CC';
	//case Fuchsia = '#FF00FF';
	case Magenta = '#FF00FF';
	case SlateBlue = '#6A5ACD';
	case MediumSlateBlue = '#7B68EE';
	case MediumOrchid = '#BA55D3';
	case MediumPurple = '#9370DB';
	case Orchid = '#DA70D6';
	case Violet = '#EE82EE';
	case Plum = '#DDA0DD';
	case Thistle = '#D8BFD8';
	case Lavender = '#E6E6FA';

	// Blue

	case MidnightBlue = '#191970';
	case Navy = '#000080';
	case DarkBlue = '#00008B';
	case MediumBlue = '#0000CD';
	case Blue = '#0000FF';
	case RoyalBlue = '#4169E1';
	case SteelBlue = '#4682B4';
	case DodgerBlue = '#1E90FF';
	case DeepSkyBlue = '#00BFFF';
	case CornflowerBlue = '#6495ED';
	case SkyBlue = '#87CEEB';
	case LightSkyBlue = '#87CEFA';
	case LightSteelBlue = '#B0C4DE';
	case LightBlue = '#ADD8E6';
	case PowderBlue = '#B0E0E6';

	// Cyan

	case Teal = '#008080';
	case DarkCyan = '#008B8B';
	case LightSeaGreen = '#20B2AA';
	case CadetBlue = '#5F9EA0';
	case DarkTurquoise = '#00CED1';
	case MediumTurquoise = '#48D1CC';
	case Turquoise = '#40E0D0';
	//case Aqua = '#00FFFF';
	case Cyan = '#00FFFF';
	case Aquamarine = '#7FFFD4';
	case PaleTurquoise = '#AFEEEE';
	case LightCyan = '#E0FFFF';

	// Green

	case DarkGreen = '#006400';
	case Green = '#008000';
	case DarkOliveGreen = '#556B2F';
	case ForestGreen = '#228B22';
	case SeaGreen = '#2E8B57';
	case Olive = '#808000';
	case OliveDrab = '#6B8E23';
	case MediumSeaGreen = '#3CB371';
	case LimeGreen = '#32CD32';
	case Lime = '#00FF00';
	case SpringGreen = '#00FF7F';
	case MediumSpringGreen = '#00FA9A';
	case DarkSeaGreen = '#8FBC8F';
	case MediumAquamarine = '#66CDAA';
	case YellowGreen = '#9ACD32';
	case LawnGreen = '#7CFC00';
	case Chartreuse = '#7FFF00';
	case LightGreen = '#90EE90';
	case GreenYellow = '#ADFF2F';
	case PaleGreen = '#98FB98';

	// White

	case MistyRose = '#FFE4E1';
	case AntiqueWhite = '#FAEBD7';
	case Linen = '#FAF0E6';
	case Beige = '#F5F5DC';
	case WhiteSmoke = '#F5F5F5';
	case LavenderBlush = '#FFF0F5';
	case OldLace = '#FDF5E6';
	case AliceBlue = '#F0F8FF';
	case Seashell = '#FFF5EE';
	case GhostWhite = '#F8F8FF';
	case Honeydew = '#F0FFF0';
	case FloralWhite = '#FFFAF0';
	case Azure = '#F0FFFF';
	case MintCream = '#F5FFFA';
	case Snow = '#FFFAFA';
	case Ivory = '#FFFFF0';
	case White = '#FFFFFF';

	// Gray and black

	case Black = '#000000';
	case DarkSlateGray = '#2F4F4F';
	case DimGray = '#696969';
	case SlateGray = '#708090';
	case Gray = '#808080';
	case LightSlateGray = '#778899';
	case DarkGray = '#A9A9A9';
	case Silver = '#C0C0C0';
	case LightGray = '#D3D3D3';
	case Gainsboro = '#DCDCDC';
}
