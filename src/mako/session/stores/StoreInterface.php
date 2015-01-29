<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\session\stores;

/**
 * Store interface.
 *
 * @author  Frederic G. Østby
 */

interface StoreInterface
{
	/**
	 * Writes session data.
	 *
	 * @access  public
	 * @param   string  $sessionId    Session id
	 * @param   array   $sessionData  Session data
	 * @param   int     $dataTTL      TTL in seconds
	 */

	public function write($sessionId, $sessionData, $dataTTL);

	/**
	 * Reads and returns session data.
	 *
	 * @access  public
	 * @param   string  $sessionId  Session id
	 * @return  array
	 */

	public function read($sessionId);

	/**
	 * Destroys the session data assiciated with the provided id.
	 *
	 * @access  public
	 * @param   string   $sessionId  Session id
	 */

	public function delete($sessionId);

	/**
	 * Garbage collector that deletes expired session data.
	 *
	 * @access  public
	 * @param   int      $dataTTL  Data TTL in seconds
	 */

	public function gc($dataTTL);
}