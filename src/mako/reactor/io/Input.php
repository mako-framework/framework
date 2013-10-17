<?php

namespace mako\reactor\io;

use \mako\reactor\io\Output;
use \Symfony\Component\Console\Helper\DialogHelper;

/**
 * Reactor input.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Input
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * StdOut instance.
	 * 
	 * @var \mako\reactor\io\Output
	 */

	protected $output;

	/**
	 * DialogHelper instance.
	 * 
	 * @var \Symfony\Component\Console\Helper\DialogHelper
	 */

	protected $dialogHelper;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Constructor.
	 * 
	 * @access  public
	 * @param   \mako\reactor\io\Output  $output  Standard output
	 */

	public function __construct(Output $output)
	{
		$this->output = $output;

		$this->dialogHelper = new DialogHelper;
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Return value of named parameters (--<name>=<value>).
	 *
	 * @access public
	 * @param  string  $name     Parameter name
	 * @param  string  $default  (optional) Default value
	 * @return string
	 */

	public function param($name, $default = null)
	{
		static $parameters = false;

		// Only parse parameters once

		if($parameters === false)
		{
			$parameters = array();

			foreach($_SERVER['argv'] as $arg)
			{
				if(substr($arg, 0, 2) === '--')
				{
					$arg = explode('=', substr($arg, 2), 2);

					$parameters[$arg[0]] = isset($arg[1]) ? $arg[1] : true;
				}
			}	
		}

		return isset($parameters[$name]) ? $parameters[$name] : $default;
	}

	/**
	 * Prompt user for input.
	 *
	 * @access  public
	 * @param   string  $question      Question for the user
	 * @param   string  $default       (optional) The default answer if none is given by the user
	 * @param   array   $autocomplete  (optional) List of values to autocomplete
	 * @return  string
	 */

	public function text($question, $default = null, array $autocomplete = null)
	{
		$response = $this->dialogHelper->ask($this->output, $question . ' ', $default, $autocomplete);

		$this->output->nl();

		return $response;
	}

	/**
	 * Prompt user for hidden input.
	 *
	 * @access  public
	 * @param   string   $question  Question for the user
	 * @param   boolean  $fallback  (optional) In case the response can not be hidden, whether to fallback on non-hidden question or not
	 * @return  string
	 */

	public function password($question, $fallback = true)
	{
		$response = $this->dialogHelper->askHiddenResponse($this->output, $question . ' ', $fallback = true);

		$this->output->nl();

		return $response;
	}

	/**
	 * Prompt user a confirmation.
	 *
	 * @access  public
	 * @param   string   $question  Question for the user
	 * @param   boolean  $default   (optional) The default answer if the user enters nothing
	 * @return  boolean
	 */

	public function confirm($question, $default = true)
	{
		$response = $this->dialogHelper->askConfirmation($this->output, $question . ' [y/n] ', $default);

		$this->output->nl();

		return $response;
	}
}

/** -------------------- End of file -------------------- **/