<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\common;

use mako\common\ConnectionManager as ConnectionManagerAbstract;
use mako\tests\TestCase;

// --------------------------------------------------------------------------
// START CLASSES
// --------------------------------------------------------------------------

class Connection
{
	protected $name;

	public function __construct($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		return $this->name;
	}

	public function method($foo, $bar)
	{
		return [$foo, $bar];
	}
}

class ConnectionManager extends ConnectionManagerAbstract
{
	public function connect(string $connection)
	{
		return new Connection($connection);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */
class ConnectionManagerTest extends TestCase
{
	/**
	 *
	 */
	public function testConnection(): void
	{
		$manager = new ConnectionManager('foo', []);

		$connection = $manager->getConnection();

		$this->assertInstanceOf('mako\tests\unit\common\Connection', $connection);

		$this->assertSame('foo', $connection->getName());

		$connection = $manager->getConnection('bar');

		$this->assertInstanceOf('mako\tests\unit\common\Connection', $connection);

		$this->assertSame('bar', $connection->getName());
	}

	/**
	 *
	 */
	public function testClose(): void
	{
		$manager = new ConnectionManager('foo', []);

		$getConnections = (function()
		{
			return $this->connections;
		})->bindTo($manager, ConnectionManager::class);

		$manager->getConnection();

		$this->assertTrue(isset($getConnections()['foo']));

		$manager->close();

		$this->assertFalse(isset($getConnections()['foo']));

		//

		$manager->getConnection('foo');

		$this->assertTrue(isset($getConnections()['foo']));

		$manager->close('foo');

		$this->assertFalse(isset($getConnections()['foo']));
	}

	/**
	 *
	 */
	public function testExecuteAndClose(): void
	{
		$manager = new ConnectionManager('foo', []);

		$getConnections = (function()
		{
			return $this->connections;
		})->bindTo($manager, ConnectionManager::class);

		$returnValue = $manager->executeAndClose(function() use ($getConnections)
		{
			$this->assertTrue(isset($getConnections()['foo']));

			return 123;
		});

		$this->assertSame(123, $returnValue);

		$this->assertFalse(isset($getConnections()['foo']));
	}

	/**
	 *
	 */
	public function testCallForwarding(): void
	{
		$manager = new ConnectionManager('foo', []);

		$this->assertSame('foo', $manager->getName());

		$this->assertSame(['foo', 'bar'], $manager->method('foo', 'bar'));
	}
}
