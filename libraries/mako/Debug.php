<?php

namespace mako;

use \mako\Arr;
use \mako\Database;

/**
 * Debug toolbar.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Debug
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const EMERGENCY = 'emergency';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const ALERT = 'alert';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const CRITICAL = 'critical';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const ERROR = 'error';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const WARNING = 'warning';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const NOTICE = 'notice';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const INFO = 'info';

	/**
	 * Log level.
	 *
	 * @var string
	 */

	const DEBUG = 'debug';

	/**
	 * Log entries.
	 *
	 * @var array
	 */

	protected static $logs = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Protected constructor since this is a static class.
	 *
	 * @access  protected
	 */

	protected function __construct()
	{
		// Nothing here
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Add entry to the debug log.
	 *
	 * @access  public
	 * @param   mixed   $message  Variable to debug
	 * @param   string  $type     Log type
	 */

	public static function log($message, $type = Debug::DEBUG)
	{
		static::$logs[] = array('message' => $message, 'type' => $type);
	}

	/**
	 * Returns the rendered toolbar.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function render()
	{
		$queries = Database::profiler();
		
		return View::factory('_mako_/toolbar', array
		(
			'time'       => round(microtime(true) - MAKO_START, 4),
			'logs'       => static::$logs,
			'queries'    => $queries,
			'db_time'    => round(array_sum(Arr::pluck($queries, 'time')), 4),
			'files'      => get_included_files(),
		))->render();
	}
}

/** -------------------- End of file --------------------**/