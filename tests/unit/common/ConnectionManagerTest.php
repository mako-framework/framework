<?php

namespace mako\tests\unit\common;

use PHPUnit_Framework_TestCase;

use mako\common\ConnectionManager as ConnectionManagerAbstract;

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
	public function connect($name)
	{
		return new Connection($name);
	}
}

// --------------------------------------------------------------------------
// END CLASSES
// --------------------------------------------------------------------------

/**
 * @group unit
 */

class ConnectionManagerTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 */

	public function testConnection()
	{
		$manager = new ConnectionManager('foo', []);

		$connection = $manager->connection();

		$this->assertInstanceOf('mako\tests\unit\common\Connection', $connection);

		$this->assertSame('foo', $connection->getName());

		$connection = $manager->connection('bar');

		$this->assertInstanceOf('mako\tests\unit\common\Connection', $connection);

		$this->assertSame('bar', $connection->getName());
	}

	/**
	 *
	 */

	public function testCallForwarding()
	{
		$manager = new ConnectionManager('foo', []);

		$this->assertSame('foo', $manager->getName());

		$this->assertSame(['foo', 'bar'], $manager->method('foo', 'bar'));
	}
}