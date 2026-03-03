<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders\stream\event;

use Deprecated;

/**
 * Event stream field types.
 */
enum Type: string
{
	/* Start compatibility */
	#[Deprecated('use Type::Comment instead', 'Mako 12.2.0')]
	public const COMMENT = self::Comment;
	#[Deprecated('use Type::Comment Date', 'Mako 12.2.0')]
	public const DATA = self::Data;
	#[Deprecated('use Type::Event instead', 'Mako 12.2.0')]
	public const EVENT = self::Event;
	#[Deprecated('use Type::Id instead', 'Mako 12.2.0')]
	public const ID = self::Id;
	#[Deprecated('use Type::Retry instead', 'Mako 12.2.0')]
	public const RETRY = self::Retry;
	/* End compatibility */

	case Comment = '';
	case Data = 'data';
	case Event = 'event';
	case Id = 'id';
	case Retry = 'retry';
}
