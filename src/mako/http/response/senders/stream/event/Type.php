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
	case COMMENT = '';
	case DATA = 'data';
	case EVENT = 'event';
	case ID = 'id';
	case RETRY = 'retry';
}
