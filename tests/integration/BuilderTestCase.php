<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration;

use mako\database\ConnectionManager;
use mako\tests\TestCase;

/**
 * Builder test case.
 */
abstract class BuilderTestCase extends TestCase
{
	/**
	 * @var \mako\database\ConnectionManager
	 */
	protected $connectionManager;

	/**
	 * {@inheritDoc}
	 */
	public function setup(): void
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

		$sql = file_get_contents(__DIR__ . '/resources/sqlite.sql');

		$this->connectionManager->connection()->getPDO()->exec($sql);
	}
}
