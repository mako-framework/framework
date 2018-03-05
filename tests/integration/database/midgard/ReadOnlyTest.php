<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration\database\midgard;

use DateTime;
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
	 * @expectedException \mako\database\midgard\traits\exceptions\ReadOnlyException
	 */
	public function saveReadOnly()
	{
		$dateTime = new DateTime;

		$user = new TestUserReadOnly();

		$user->username = 'bax';

		$user->email = 'bax@example.org';

		$user->created_at = $dateTime;

		$user->save();
	}

	/**
	 * @expectedException \mako\database\midgard\traits\exceptions\ReadOnlyException
	 */
	public function testCreateReadOnly()
	{
		$dateTime = new DateTime;

		$user = TestUserReadOnly::create(['username' => 'bax', 'email' => 'bax@example.org', 'created_at' => $dateTime]);
	}

	/**
	 * @expectedException \mako\database\midgard\traits\exceptions\ReadOnlyException
	 */
	public function testUpdateReadOnly()
	{
		$user = TestUserReadOnly::get(1);

		$user->username = 'bax';

		$user->save();
	}

	/**
	 * @expectedException \mako\database\midgard\traits\exceptions\ReadOnlyException
	 */
	public function testDeleteReadOnly()
	{
		$user = TestUserReadOnly::get(1);

		$user->delete();
	}
}
