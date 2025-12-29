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
	case UP = "\x1b[A";
	case DOWN = "\x1b[B";
	case LEFT = "\x1b[D";
	case RIGHT = "\x1b[C";
	case ENTER = "\n";
	case SPACE = ' ';
	case TAB = "\t";
	case CTRL_A = "\x01";
	case CTRL_B = "\x02";
	case CTRL_C = "\x03";
	case CTRL_D = "\x04";
	case BACKSPACE = "\x7F";
	case DELETE = "\x1b[3~";
	case HOME = "\x1b[H";
	case END = "\x1b[F";
	case PAGE_UP = "\x1b[5~";
	case PAGE_DOWN = "\x1b[6~";
	case ESCAPE = "\x1b";
}
