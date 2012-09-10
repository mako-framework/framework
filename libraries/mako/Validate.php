<?php

namespace mako;

/**
 * Input/data validation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2012 Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

class Validate
{
	//---------------------------------------------
	// Class properties
	//---------------------------------------------

	/**
	 * Holds the input array.
	 *
	 * @var array
	 */

	protected $input;

	/**
	 * Holds the returned errors.
	 *
	 * @var array
	 */

	protected $errors = array();

	/**
	 * Holds all the callback validation functions that need to be run.
	 *
	 * @var array
	 */

	protected $rules = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   array  $input  Array to validate
	 */

	public function __construct(array $input)
	{
		$this->input = $input;
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   array          $input  Array to validate
	 * @return  mako\Validate
	 */

	public static function factory(array $input)
	{
		return new static($input);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Adds a validation rule to the list of callbacks.
	 *
	 * @access  public
	 * @param   mixed          $field     Field name
	 * @param   callback       $function  Function to use for validation
	 * @param   string         $error     Error message to return if validation fails
	 * @return  mako\Validate
	 */

	public function rule($field, $function, $error)
	{
		!is_array($function) && $function = array($function);

		if(is_string($function[0]) && method_exists(__CLASS__, $function[0]))
		{
			$function[0] = array(__CLASS__, $function[0]);
		}

		$callback['function'] = $function[0];
		$callback['params']   = isset($function[1]) ? $function[1] : array();
		$callback['error']    = $error;

		if($field === '*')
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
	 * Runs all validation rules. Returns TRUE if all rules passed and FALSE if validation failed.
	 *
	 * @access  public
	 * @param   array    $errors  (optional) If $errors is provided, then it is filled with all the error messages
	 * @return  boolean
	 */

	public function process(& $errors = null)
	{
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

		$errors = $this->errors;

		return empty($this->errors);
	}
	
	/**
	 * Checks if field is empty or not
	 *
	 * @access  protected
	 * @param   string     $input  The input string
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
	 * @param   string     $input   The input string
	 * @param   int        $length  Required min length
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
	 * @param   string     $input   The input string
	 * @param   int        $length  Required max length
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
	 * @param   string     $input   The input string
	 * @param   int        $length  The required length
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
	 * @param   string     $input  The input string
	 * @param   string     $field  Field name to match against
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
	 * @param   string     $input    The input string
	 * @param   string     $pattern  Regex pattern to match against
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
	 * @param   string   $input Email address to validate
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
	 * @param   string   $input  Email address to validate
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
	 *
	 * @access  public
	 * @param   string   $input  IP address to validate
	 * @return  boolean
	 */
	
	public function ip($input)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_IP);
	}
	
	/**
	 * Validates an URL using PHPs own URL validation filter.
	 *
	 * @access  public
	 * @param   string   $input  URL to validate
	 * @return  boolean
	 */
	
	public function url($input)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_URL);
	}
}

/** -------------------- End of file --------------------**/