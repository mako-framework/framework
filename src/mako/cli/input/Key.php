<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input;

use Deprecated;

/**
 * Keyboard keys.
 */
enum Key: string
{
	/* Start compatibility */
	#[Deprecated('use Key::Up instead', 'Mako 12.2.0')]
	public const UP = self::Up;
	#[Deprecated('use Key::Down instead', 'Mako 12.2.0')]
	public const DOWN = self::Down;
	#[Deprecated('use Key::Left instead', 'Mako 12.2.0')]
	public const LEFT = self::Left;
	#[Deprecated('use Key::Right instead', 'Mako 12.2.0')]
	public const RIGHT = self::Right;
	#[Deprecated('use Key::Enter instead', 'Mako 12.2.0')]
	public const ENTER = self::Enter;
	#[Deprecated('use Key::Space instead', 'Mako 12.2.0')]
	public const SPACE = self::Space;
	#[Deprecated('use Key::Tab instead', 'Mako 12.2.0')]
	public const TAB = self::Tab;
	#[Deprecated('use Key::CtrlA instead', 'Mako 12.2.0')]
	public const CTRL_A = self::CtrlA;
	#[Deprecated('use Key::CtrlB instead', 'Mako 12.2.0')]
	public const CTRL_B = self::CtrlB;
	#[Deprecated('use Key::CtrlC instead', 'Mako 12.2.0')]
	public const CTRL_C = self::CtrlC;
	#[Deprecated('use Key::CtrlD instead', 'Mako 12.2.0')]
	public const CTRL_D = self::CtrlD;
	#[Deprecated('use Key::Backspace instead', 'Mako 12.2.0')]
	public const BACKSPACE = self::Backspace;
	#[Deprecated('use Key::Delete instead', 'Mako 12.2.0')]
	public const DELETE = self::Delete;
	#[Deprecated('use Key::Home instead', 'Mako 12.2.0')]
	public const HOME = self::Home;
	#[Deprecated('use Key::End instead', 'Mako 12.2.0')]
	public const END = self::End;
	#[Deprecated('use Key::PageUp instead', 'Mako 12.2.0')]
	public const PAGE_UP = self::PageUp;
	#[Deprecated('use Key::PageDown instead', 'Mako 12.2.0')]
	public const PAGE_DOWN = self::PageDown;
	#[Deprecated('use Key::Escape instead', 'Mako 12.2.0')]
	public const ESCAPE = self::Escape;
	/* End compatibility */

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
