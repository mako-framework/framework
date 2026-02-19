<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\senders\stream\event;

use mako\http\response\senders\stream\event\Event;
use mako\http\response\senders\stream\event\Field;
use mako\http\response\senders\stream\event\Type;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class EventTest extends TestCase
{
	/**
	 *
	 */
	public function testEvent(): void
	{
		$event = new Event(
			new Field(Type::DATA, 'foobar'),
			new Field(Type::DATA, 'barfoo'),
		);

		$this->assertCount(2, $event->fields);

		foreach ($event->fields as $field) {
			$this->assertInstanceOf(Field::class, $field);
		}
	}
}
