<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\reactor\attributes;

use mako\reactor\attributes\CommandDescription;
use mako\tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class CommandDescriptionTest extends TestCase
{
	/**
	 *
	 */
	public function testGetDescription(): void
	{
		$description = new CommandDescription('Command description.');

		$this->assertSame('Command description.', $description->getDescription());

		$this->assertEmpty($description->getAdditionalInformation());
	}

	/**
	 *
	 */
	public function testGetDescriptionWithAdditionalInformation(): void
	{
		$description = new CommandDescription('Command description.', 'Additional information.');

		$this->assertSame('Command description.', $description->getDescription());

		$this->assertSame('Additional information.', $description->getAdditionalInformation());
	}
}
