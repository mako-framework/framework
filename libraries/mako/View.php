<?php

namespace mako;

use \mako\Mako;
use \mako\view\Compiler;
use \Exception;
use \RuntimeException;

/**
* View/template class.
*
* @author     Frederic G. Østby
* @copyright  (c) 2008-2012 Frederic G. Østby
* @license    http://www.makoframework.com/license
*/

class View
{
	//---------------------------------------------
	// Class variables
	//---------------------------------------------

	/**
	* Path to view file.
	*
	* @var string
	*/

	protected $viewFile;

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
	* The output.
	*
	* @var string
	*/

	protected $output;

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	* Create a new view object.
	*
	* @access  public
	* @param   string  Name of the view file
	* @param   array   (optional) Array of view variables
	*/

	public function __construct($view, array $variables = array())
	{
		// Check if view file exists

		if(file_exists($file = Mako::path('views', $view)))
		{
			$this->viewFile = $file;
		}

		// No view, check if template exists

		elseif(file_exists($file = Mako::path('views', $view . '.tpl')))
		{
			$this->viewFile = Compiler::compile($file);
		}

		// No view or template. Throw exception

		else
		{
			throw new RuntimeException(vsprintf("%s(): The '%s' view does not exist.", array(__METHOD__, $view)));
		}

		// Assign view variables

		$this->vars = $variables;
	}

	/**
	* Factory method making method chaining possible right off the bat.
	*
	* @access  public
	* @param   string     Name of the view file
	* @param   array      (optional) Array of view variables
	* @return  mako\View
	*/

	public static function factory($view, array $variables = array())
	{
		return new static($view, $variables);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	* Assign a view variable.
	*
	* @access  public
	* @param   string     Variable name
	* @param   mixed      View variable
	* @param   boolean    (optional) True to make variable available in all views
	* @return  mako\View
	*/

	public function assign($name, $value, $global = false)
	{
		if($global === false)
		{
			$this->vars[$name] = $value;
		}
		else
		{
			static::$globalVars[$name] = $value; // Available to all views
		}

		return $this;
	}

	/**
	* Assign a global view variable that will be available in all views.
	*
	* @access  public
	* @param   string  Variable name
	* @param   mixed   View variable
	*/

	public static function assignGlobal($name, $value)
	{
		static::$globalVars[$name] = $value;
	}
	
	/**
	* Assign a view variable by reference.
	*
	* @deprecated
	* @access  public
	* @param   string     Variable name
	* @param   mixed      View variable
	* @param   boolean    (optional) True to make variable available in all views
	* @return  mako\View
	*/

	public function assignByRef($name, &$value, $global = false)
	{
		if($global === false)
		{
			$this->vars[$name] =& $value;
		}
		else
		{
			static::$globalVars[$name] =& $value; // Available to all views
		}

		return $this;
	}

	/**
	* Include the view file and extracts the view variables before returning the generated output.
	*
	* @access  public
	* @param   callback  (optional) Callback function used to filter output
	* @return  string
	*/

	public function render($filter = null)
	{
		if(empty($this->output))
		{
			extract(array_merge($this->vars, static::$globalVars), EXTR_REFS); // Extract variables as references
			
			ob_start();

			include($this->viewFile);

			$this->output = ob_get_clean();
		}

		if($filter !== null)
		{
			$this->output = call_user_func($filter, $this->output);
		}

		return $this->output;
	}

	/**
	* Magic setter method that assigns a view variable.
	*
	* @access  public
	* @param   string  Variable name
	* @param   mixed   View variable
	*/

	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}

	/**
	* Magic getter method that returns a view variable.
	*
	* @access  public
	* @param   string  Variable name
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
	* @param   string   Variable name
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
	* @param   string   Variable name
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
			Mako::exceptionHandler($e);
		}
	}
}

/** -------------------- End of file --------------------**/