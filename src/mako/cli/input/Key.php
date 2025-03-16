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
	case UP = "\033[A";
	case DOWN = "\033[B";
	case LEFT = "\033[D";
	case RIGHT = "\033[C";
	case ENTER = "\n";
	case SPACE = ' ';
	case TAB = "\t";
	case CTRL_A = "\x01";
	case CTRL_B = "\x02";
	case CTRL_C = "\x03";
	case CTRL_D = "\x04";
	case BACKSPACE = "\x7F";
	case DELETE = "\033[3~";
	case HOME = "\033[H";
	case END = "\033[F";
	case PAGE_UP = "\033[5~";
	case PAGE_DOWN = "\033[6~";
	case ESCAPE = "\033";
}
