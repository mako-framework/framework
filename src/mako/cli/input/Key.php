<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input;

/**
 * Keyboard keys.
 */
enum Key: string
{
	case Up = "\x1b[A";
	case Down = "\x1b[B";
	case Left = "\x1b[D";
	case Right = "\x1b[C";
	case Enter = "\n";
	case Space = ' ';
	case Tab = "\t";
	case CtrlA = "\x01";
	case CtrlB = "\x02";
	case CtrlC = "\x03";
	case CtrlD = "\x04";
	case Backspace = "\x7F";
	case Delete = "\x1b[3~";
	case Home = "\x1b[H";
	case End = "\x1b[F";
	case PageUp = "\x1b[5~";
	case PageDown = "\x1b[6~";
	case Escape = "\x1b";
}
