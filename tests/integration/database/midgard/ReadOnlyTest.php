<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use DateTime;
use mako\database\midgard\traits\exceptions\ReadOnlyException;
use mako\tests\integration\ORMTestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class TestUserReadOnly extends TestUser
{
	use \mako\database\midgard\traits\ReadOnlyTrait;
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group integration
 * @group integration:database
 * @requires extension PDO
 * @requires extension pdo_sqlite
 */
class ReadOnlyTest extends ORMTestCase
{
	/**
	 *
	 */
	public function saveReadOnly(): void
	{
		$this->expectException(ReadOnlyException::class);

		$dateTime = new DateTime;

		$user = new TestUserReadOnly();

		$user->username = 'bax';

		$user->email = 'bax@example.org';

		$user->created_at = $dateTime;

		$user->save();
	}

	/**
	 *
	 */
	public function testCreateReadOnly(): void
	{
		$this->expectException(ReadOnlyException::class);

		$dateTime = new DateTime;

		TestUserReadOnly::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);
	}

	/**
	 *
	 */
	public function testUpdateReadOnly(): void
	{
		$this->expectException(ReadOnlyException::class);

		$user = TestUserReadOnly::get(1);

		$user->username = 'bax';

		$user->save();
	}

	/**
	 *
	 */
	public function testDeleteReadOnly(): void
	{
		$this->expectException(ReadOnlyException::class);

		$user = TestUserReadOnly::get(1);

		$user->delete();
	}
}
