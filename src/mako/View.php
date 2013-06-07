<?php

namespace mako;

use \Closure;
use \Exception;
use \RuntimeException;
use \mako\ErrorHandler;
use \mako\view\Compiler;

/**
 * View class.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class View
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Path to view.
	 *
	 * @var string
	 */

	protected $view;

	/**
	 * View variables.
	 *
	 * @var array
	 */

	protected $vars = array();

	/**
	 * Global view variables.
	 *
	 * @var array
	 */

	protected static $globalVars = array();

	/**
	 * View renderers.
	 * 
	 * @var array
	 */

	protected static $renderers = array
	(
		''     => '\mako\view\renderer\PHP',
		'.tpl' => '\mako\view\renderer\Template',
	);

	/**
	 * View renderer.
	 * 
	 * @var string
	 */

	protected $renderer;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Create a new view object.
	 *
	 * @access  public
	 * @param   string  $view       Name of the view file
	 * @param   array   $variables  (optional) Array of view variables
	 */

	public function __construct($view, array $variables = array())
	{
		// Assign view variables

		$this->vars = $variables;

		// Find appropriate renderer

		$view = str_replace('.', '/', $view);

		foreach(static::$renderers as $extension => $renderer)
		{
			if(file_exists($this->view = mako_path('views', $view . $extension)))
			{
				$this->renderer = $renderer;

				break;
			}
		}

		// View not found ... throw exception

		if($this->renderer === null)
		{
			throw new RuntimeException(vsprintf("%s(): The '%s' view does not exist.", array(__METHOD__, $view)));
		}
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   string      $view       Name of the view file
	 * @param   array       $variables  (optional) Array of view variables
	 * @return  \mako\View
	 */

	public static function factory($view, array $variables = array())
	{
		return new static($view, $variables);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Registers a custom view renderer.
	 * 
	 * @access  public
	 * @param   string  $extension  Extension to handle
	 * @param   string  $renderer   Renderer
	 */

	public static function registerRenderer($extension, $renderer)
	{
		static::$renderers[$extension] = $renderer;
	}

	/**
	 * Assign a view variable.
	 *
	 * @access  public
	 * @param   string      $name   Variable name
	 * @param   mixed       $value  View variable
	 * @return  \mako\View
	 */

	public function assign($name, $value)
	{
		$this->vars[$name] = $value;

		return $this;
	}

	/**
	 * Assign a global view variable that will be available in all views.
	 *
	 * @access  public
	 * @param   string  $name   Variable name
	 * @param   mixed   $value  View variable
	 */

	public static function assignGlobal($name, $value)
	{
		static::$globalVars[$name] = $value;
	}

	/**
	 * Returns a rendered view.
	 *
	 * @access  public
	 * @param   \Closure  $filter  (optional) Closure used to filter output
	 * @return  string
	 */

	public function render(Closure $filter = null)
	{
		$renderer = new $this->renderer($this->view, $this->vars, static::$globalVars);

		$output = $renderer->render();

		if($filter !== null)
		{
			$output = $filter($output);
		}

		return $output;
	}

	/**
	 * Magic setter method that assigns a view variable.
	 *
	 * @access  public
	 * @param   string  $name   Variable name
	 * @param   mixed   $value  View variable
	 */

	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}

	/**
	 * Magic getter method that returns a view variable.
	 *
	 * @access  public
	 * @param   string  $name  Variable name
	 * @return  mixed
	 */

	public function __get($name)
	{
		if(isset($this->vars[$name]))
		{
			return $this->vars[$name];
		}
	}

	/**
	 * Magic isset method that checks if a view variable is set.
	 *
	 * @access  public
	 * @param   string   $name  Variable name
	 * @return  boolean
	 */

	public function __isset($name)
	{
		return isset($this->vars[$name]);
	}

	/**
	 * Magic unset method that unsets a view variable.
	 *
	 * @access  public
	 * @param   string   $name  Variable name
	 */

	public function __unset($name)
	{
		unset($this->vars[$name]);
	}

	/**
	 * Method that magically converts the view object into a string.
	 *
	 * @access  public
	 * @return  string
	 */

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch(Exception $e)
		{
			ErrorHandler::handler($e);
		}
	}
}

/** -------------------- End of file -------------------- **/