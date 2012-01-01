<?php

namespace mako
{
	/**
	* Class for filtering and validating input.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2012 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Input
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Holds the input array.
		*/

		protected $input;

		/**
		* Holds the returned errors.
		*/

		protected $errors = array();
		
		/**
		* Holds all the callback filtering functions that need to be run.
		*/
		
		protected $filters = array();

		/**
		* Holds all the callback validation functions that need to be run.
		*/

		protected $rules = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Class constructor.
		*
		* @access  public
		* @param   array  Array to validate
		*/

		public function __construct(array & $input)
		{
			$this->input = & $input;
		}

		/**
		* Factory method making method chaining possible right off the bat.
		*
		* @access  public
		* @param   array     Array to validate
		* @return  Validate
		*/

		public static function factory(array & $input)
		{
			return new static($input);
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------
		
		/**
		* Adds a filter to the list of callbacks.
		*
		* @access  public
		* @param   mixed     Field name - if set to TRUE then the filter will be run on all fields
		* @param   callback  Filter function
		* @param   array     (optional) Extra parameters for the callback function
		* @return  Validate
		*/
		
		public function filter($field, $function, array $params = null)
		{
			$callback['function'] = $function;
			$callback['params']   = ($params !== null) ? $params : array();
			
			if($field === true)
			{
				foreach(array_keys($this->input) as $field)
				{
					$this->filters[$field][] = $callback;
				}
			}
			else
			{
				$this->filters[$field][] = $callback;
			}
			
			return $this;
		}

		/**
		* Adds a validation rule to the list of callbacks.
		*
		* @access  public
		* @param   mixed     Field name - if set to TRUE then validation rule will be run on all fields
		* @param   callback  Function to use for validation
		* @param   array     (optional) Extra parameters for the callback function
		* @param   string    Error message to return if validation fails
		* @return  Validate
		*/

		public function validate($field, $function, array $params = null, $error)
		{
			if(is_string($function) && method_exists(__CLASS__, $function))
			{
				$function = array(__CLASS__, $function);
			}

			$callback['function'] = $function;
			$callback['params']   = ($params !== null) ? $params : array();
			$callback['error']    = $error;
			
			if($field === true)
			{
				foreach(array_keys($this->input) as $field)
				{
					$this->rules[$field][] = $callback;
				}
			}
			else
			{
				$this->rules[$field][] = $callback;
			}

			return $this;
		}

		/**
		* Runs all filters and validation rules.
		* Returns an array populated with error messages 
		* if errors are found or an empty array if not.
		*
		* @access  public
		* @return  array
		*/

		public function process()
		{
			// Run filter callbacks
			
			foreach($this->filters as $field => $filters)
			{
				foreach($filters as $callback)
				{
					$params = array_merge(array($this->input[$field]), $callback['params']);
					
					$this->input[$field] = call_user_func_array($callback['function'], $params);
				}
			}
			
			// Run validation callbacks
			
			foreach($this->rules as $field => $rules)
			{
				foreach($rules as $callback)
				{
					$params = array_merge(array($this->input[$field]), $callback['params']);

					if(call_user_func_array($callback['function'], $params) === false)
					{
						$this->errors[$field] = $callback['error'];

						break; // Jump to next field if an error is found
					}
				}
			}

			return $this->errors;
		}
		
		/**
		* Checks if field is empty or not
		*
		* @access  protected
		* @param   string     The input string
		* @return  boolean
		*/

		protected function required($input)
		{
			return ! empty($input);
		}

		/**
		* Checks if input is long enough.
		*
		* @access  protected
		* @param   string     The input string
		* @param   int        Required min length
		* @return  boolean
		*/

		protected function minLength($input, $length)
		{
			return (mb_strlen($input) >= $length);
		}

		/**
		* Checks if input is short enough.
		*
		* @access  protected
		* @param   string     The input string
		* @param   int        Required max length
		* @return  boolean  
		*/

		protected function maxLength($input, $length)
		{
			return (mb_strlen($input) <= $length);
		}
		
		/**
		* Checks if input is of the right length.
		*
		* @access  protected
		* @param   string     The input string
		* @param   int        The required length
		* @return  boolean
		*/
		
		protected function exactLength($input, $length)
		{
			return (mb_strlen($input) === $length);
		}
		
		/**
		* Check if field matches another field.
		*
		* @access  protected
		* @param   string     The input string
		* @param   string     Field name to match against
		* @return  boolean
		*/

		protected function match($input, $field)
		{
			return ($input === $this->input[$field]);
		}

		/**
		* Check if a field matches a custom regex pattern.
		*
		* @access  protected
		* @param   string     The input string
		* @param   string     Regex pattern to match against
		* @return  boolean
		*/

		protected function regex($input, $pattern)
		{
			return (bool) preg_match($pattern, $input);
		}

		/**
		* Validates an email address using PHPs own email validation filter.
		*
		* @access  public
		* @param   string   The input string
		* @return  boolean
		*/

		public static function email($input)
		{
			return (bool) filter_var($input, FILTER_VALIDATE_EMAIL);
		}

		/**
		* Validates an email domain by looking for a MX reccord.
		*
		* @access  public
		* @param   string   The input string
		* @return  boolean
		*/

		public static function emailDomain($input)
		{
			if(empty($input))
			{
				return false;
			}

			$email = explode('@', $input);

			return checkdnsrr(array_pop($email), 'MX');
		}
		
		/**
		* Validates an IP using PHPs own IP validation filter.
		*/
		
		public function ip($input, $flags = null)
		{
			return (bool) filter_var($input, FILTER_VALIDATE_IP, $flags);
		}
		
		/**
		* Validates an URL using PHPs own URL validation filter.
		*/
		
		public function url($input, $flags = null)
		{
			return (bool) filter_var($input, FILTER_VALIDATE_URL, $flags);
		}
	}
}

/** -------------------- End of file --------------------**/