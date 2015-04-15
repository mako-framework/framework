<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

use mako\session\stores\StoreInterface;

/**
 * Void store.
 *
 * @author  Frederic G. Østby
 */

class Void implements StoreInterface
{
	/**
	 * {@inheritdoc}
	 */

	public function write($sessionId, $sessionData, $dataTTL)
	{

	}

	/**
	 * {@inheritdoc}
	 */

	public function read($sessionId)
	{
		return [];
	}

	/**
	 * {@inheritdoc}
	 */

	public function delete($sessionId)
	{

	}

	/**
	 * {@inheritdoc}
	 */

	public function gc($dataTTL)
	{

	}
}