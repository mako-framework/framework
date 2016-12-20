<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

use mako\database\ConnectionManager;

abstract class BuilderTestCase extends PHPUnit_Framework_TestCase
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
		// Set up connection manager

		$configs =
		[
			'sqlite' =>
			[
				'dsn'         => 'sqlite::memory:',
				'log_queries' => true,
				'queries'     =>
				[
					"PRAGMA encoding = 'UTF-8'",
				],
			],
		];

		$this->connectionManager = new ConnectionManager('sqlite', $configs);

		// Load test database into memory

		$sql = file_get_contents(__DIR__ . '/sqlite.sql');

		$this->connectionManager->connection()->getPDO()->exec($sql);
	}
}
