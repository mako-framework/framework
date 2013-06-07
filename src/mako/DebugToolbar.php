<?php

namespace mako;

use \mako\Arr;
use \mako\File;
use \mako\Database;

/**
 * Debug toolbar.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class DebugToolbar
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
	 * Add entry to the debug toolbar log.
	 *
	 * @access  public
	 * @param   mixed    $log      Item you want to log
	 * @param   string   $type     Log type
	 * @param   boolean  $skip     (optional) Skip the first frame of the backtrace?
	 * @return  mixed
	 */

	public static function log($log, $type = DebugToolbar::DEBUG, $skip = false)
	{
		$backtrace = debug_backtrace();

		$file = $backtrace[$skip ? 1 : 0]['file'];
		$line = $backtrace[$skip ? 1 : 0]['line'];

		static::$logs[] = compact('log', 'file', 'line', 'type');

		return $log;
	}

	/**
	 * Returns the rendered toolbar.
	 *
	 * @access  public
	 * @return  string
	 */

	public static function render()
	{
		$queries = Database::getLog();
		
		return View::factory('_mako_/toolbar', array
		(
			'time'       => round(microtime(true) - MAKO_START, 4),
			'files'      => get_included_files(),
			'memory'     => File::size(memory_get_peak_usage(true)),
			'logs'       => static::$logs,
			'queries'    => $queries,
			'db_time'    => round(array_sum(Arr::pluck($queries, 'time')), 4),
		))->render();
	}
}

/** -------------------- End of file -------------------- **/