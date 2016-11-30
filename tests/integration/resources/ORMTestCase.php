<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

use mako\database\ConnectionManager;

class TestORM extends \mako\database\midgard\ORM
{

}

abstract class ORMTestCase extends BuilderTestCase
{
	/**
	 *
	 */
	protected $connectionManager;

	/**
	 *
	 */
	public function setup()
	{
		parent::setup();

		// Set the connection manager

		TestOrm::setConnectionManager($this->connectionManager);
	}

	/**
	 *
	 */
	public function tearDown()
	{
		$this->connectionManager->connection('sqlite')->clearLog();
	}
}
