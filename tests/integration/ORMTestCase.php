<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration;

use Override;

/**
 * ORM test case.
 */
abstract class ORMTestCase extends InMemoryDbTestCase
{
	/**
	 *{@inheritDoc}
	 */
	#[Override]
	public function setup(): void
	{
		parent::setup();

		// Set the connection manager

		TestORM::setConnectionManager($this->connectionManager);
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function tearDown(): void
	{
		$this->connectionManager->getConnection('sqlite')->clearLog();
	}
}
