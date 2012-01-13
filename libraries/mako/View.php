<?php

namespace mako
{
	use \mako\Mako;
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
		*/

		protected $viewFile;

		/**
		* View variables.
		*/

		protected $vars = array();

		/**
		* Global view variables.
		*/

		protected static $globalVars = array();

		/**
		* The output.
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
		*/

		public function __construct($view)
		{
			if(strrpos($view, '::') !== false)
			{
				list($bundle, $view) = explode('::', $view);

				$this->viewFile = MAKO_BUNDLES . '/' . $bundle . '/views/' . $view . '.php';
			}
			else
			{
				$this->viewFile = MAKO_APPLICATION . '/views/' . $view . '.php';
			}
			
			if(file_exists($this->viewFile) === false)
			{
				throw new RuntimeException(vsprintf("%s(): The '%s' view does not exist.", array(__METHOD__, $view)));
			}
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   string  Name of the view file
		* @return  View
		*/

		public static function factory($view)
		{
			return new static($view);
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Assign a view variable.
		*
		* @access  public
		* @param   string   Variable name
		* @param   mixed    View variable
		* @param   boolean  (optional) True to make variable available in all views
		* @return  View
		*/

		public function assign($key, $value, $global = false)
		{
			if($global === false)
			{
				$this->vars[$key] = $value;
			}
			else
			{
				static::$globalVars[$key] = $value; // Available to all views
			}

			return $this;
		}
		
		/**
		* Assign a view variable by reference.
		*
		* @access  public
		* @param   string   Variable name
		* @param   mixed    View variable
		* @param   boolean  (optional) True to make variable available in all views
		* @return  View
		*/

		public function assignByRef($key, &$value, $global = false)
		{
			if($global === false)
			{
				$this->vars[$key] =& $value;
			}
			else
			{
				static::$globalVars[$key] =& $value; // Available to all views
			}

			return $this;
		}

		/**
		* Magic setter function that assigns a view variable.
		*
		* @access  public
		* @param   string  Variable name
		* @param   mixed   View variable
		*/

		public function __set($key, $value)
		{
			$this->vars[$key] = $value;
		}

		/**
		* Magic getter function that returns a view variable.
		*
		* @access  public
		* @param   string  Variable name
		* @return  mixed
		*/

		public function __get($key)
		{
			if(isset($this->vars[$key]))
			{
				return $this->vars[$key];
			}
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
				//extract(array_merge($this->vars, static::$globalVars), EXTR_REFS); // Extract variables as references
				
				$vars = array_merge(static::$globalVars, $this->vars);
				
				extract($vars, EXTR_REFS); // Extract variables as references
				
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
		* Prints the rendered view to the browser.
		*
		* @access  public
		* @param   callback  (optional) Callback function used to filter output
		*/

		public function display($filter = null)
		{
			echo $this->render($filter);
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
}

/** -------------------- End of file --------------------**/