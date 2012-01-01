<?php

namespace mako
{
	use \RuntimeException;
	/**
	* Simple benchmarking/timer class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Benchmark
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Array of timers.
		*/

		protected static $timers = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Start a timer.
		*
		* @access  public
		* @param   string  Benchmark name
		*/

		public static function start($name)
		{
			if(isset(static::$timers[$name]) === true)
			{
				throw new RuntimeException(__CLASS__ . ": A timer named '{$name}' already exists.");
			}

			static::$timers[$name] = array
			(
				'start'        => microtime(true),
				'stop'         => false
			);
		}

		/**
		* Get the elapsed time in seconds of a running timer.
		*
		* @access  public
		* @param   string  Benchmark name
		* @param   int     (optional) Benchmark precision
		* @return  double
		*/

		public static function check($name, $precision = 4)
		{
			if(isset(static::$timers[$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has not been started.");
			}

			if(static::$timers[$name]['stop'] !== false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has been stopped.");
			}

			return round(microtime(true) - static::$timers[$name]['start'], $precision);
		}

		/**
		* Stop a timer and get elapsed time in seconds.
		*
		* @access  public
		* @param   string  Benchmark name
		* @param   int     (optional) Benchmark precision
		* @return  double
		*/

		public static function stop($name, $precision = 4)
		{
			if(isset(static::$timers[$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has not been started.");
			}

			if(static::$timers[$name]['stop'] !== false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has already been stopped.");
			}

			static::$timers[$name]['stop'] = microtime(true);

			return round(static::$timers[$name]['stop'] - static::$timers[$name]['start'], $precision);
		}

		/**
		* Get the elapsed time in seconds.
		*
		* @access  public
		* @param   string  Benchmark name
		* @param   int     (optional) Benchmark precision
		* @return  double
		*/

		public static function get($name, $precision = 4)
		{
			if(isset(static::$timers[$name]) === false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has not been started.");
			}

			if(static::$timers[$name]['stop'] === false)
			{
				throw new RuntimeException(__CLASS__ . ": The '{$name}' timer has not been stopped.");
			}

			return round(static::$timers[$name]['stop'] - static::$timers[$name]['start'], $precision);
		}

		/**
		* Returns an array containing all the benchmarks.
		*
		* @access  public
		* @param   int     (optional) Benchmark precision
		* @return  array
		*/

		public static function getAll($precision = 4)
		{
			$benchmarks = array();

			foreach(static::$timers as $k => $v)
			{
				$benchmarks[$k] = static::get($k, $precision);
			}

			return $benchmarks;
		}

		/**
		* Returns the sum of all the benchmark times.
		*
		* @access  public
		* @param   int     (optional) Benchmark precision
		* @return  double
		*/

		public static function totalTime($precision = 4)
		{
			return array_sum(static::getAll($precision));
		}
	}	
}

/** -------------------- End of file --------------------**/