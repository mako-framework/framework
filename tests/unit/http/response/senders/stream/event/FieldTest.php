<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\http\response\senders\stream\event;

use mako\http\response\senders\stream\event\Field;
use mako\http\response\senders\stream\event\Type;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class FieldTest extends TestCase
{
	/**
	 *
	 */
	public function testField(): void
	{
		$field = new Field(Type::Data, 'foobar');

		$this->assertSame(Type::Data, $field->type);
		$this->assertSame('foobar', $field->value);
	}
}
