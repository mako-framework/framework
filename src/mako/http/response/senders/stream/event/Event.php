<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\senders\stream\event;

/**
 * Event stream event.
 */
class Event
{
	/**
	 * Event fields.
	 *
	 * @var Field[]
	 */
	public protected(set) array $fields;

	/**
	 * Constructor.
	 */
	public function __construct(Field ...$fields)
	{
		$this->fields = $fields;
	}
}
