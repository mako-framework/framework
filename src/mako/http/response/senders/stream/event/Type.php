<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders\stream\event;

/**
 * Event stream field types.
 */
enum Type: string
{
	case Comment = '';
	case Data = 'data';
	case Event = 'event';
	case Id = 'id';
	case Retry = 'retry';
}
