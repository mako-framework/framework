<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\integration;

use mako\tests\integration\BuilderTestCase;
use mako\tests\integration\TestORM;

/**
 * ORM test case.
 *
 * @author Frederic G. Østby
 */
abstract class ORMTestCase extends BuilderTestCase
{
	/**
	 *{@inheritdoc}
	 */
	public function setup()
	{
		parent::setup();

		// Set the connection manager

		TestORM::setConnectionManager($this->connectionManager);
	}

	/**
	 * {@inheritdoc}
	 */
	public function tearDown()
	{
		parent::tearDown();

		$this->connectionManager->connection('sqlite')->clearLog();
	}
}
