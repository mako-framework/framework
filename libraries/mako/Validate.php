<?php

namespace mako;

use \mako\I18n;
use \mako\String;
use \mako\security\Token;
use \Closure;
use \DateTime;

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

	/**
	 * Custom validators.
	 * 
	 * @var array
	 */

	protected static $validators = array();

	//---------------------------------------------
	// Class constructor, destructor etc ...
	//---------------------------------------------

	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   array  $input  Array to validate
	 * @param   array  $rules  Array of validation rules
	 */

	public function __construct(array $input, array $rules)
	{
		$this->input = $input;
		$this->rules = $rules;
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   array          $input  Array to validate
	 * @param   array          $rules  Array of validation rules
	 * @return  mako\Validate
	 */

	public static function factory(array $input, array $rules)
	{
		return new static($input, $rules);
	}

	//---------------------------------------------
	// Class methods
	//---------------------------------------------

	/**
	 * Checks that the field isn't empty.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateRequired($input, $parameters)
	{
		return ! in_array($input, array('', null, array()), true);
	}

	/**
	 * Checks that the field value is long enough.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateMinLength($input, $parameters)
	{
		return (mb_strlen($input) >= (int) $parameters[0]);
	}

	/**
	 * Checks that the field value is short enough.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateMaxLength($input, $parameters)
	{
		return (mb_strlen($input) <= (int) $parameters[0]);
	}

	/**
	 * Checks that the field value is of the right length.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateExactLength($input, $parameters)
	{
		return (mb_strlen($input) === (int) $parameters[0]);
	}

	/**
	 * Checks that the field value is less than x.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateLessThan($input, $parameters)
	{
		return ((int) $input < (int) $parameters[0]);
	}

	/**
	 * Checks that the field value is greater than x.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateGreaterThan($input, $parameters)
	{
		return ((int) $input > (int) $parameters[0]);
	}

	/**
	 * Checks that the field value is between x and y.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateBetween($input, $parameters)
	{
		return ((int) $input >= (int) $parameters[0] && (int) $input <= (int) $parameters[1]);
	}

	/**
	 * Checks that the field value matches the value of another field.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateMatch($input, $parameters)
	{
		return ($input === $this->input[$parameters[0]]);
	}

	/**
	 * Checks that the field value is different from the value of another field.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateDifferent($input, $parameters)
	{
		return ($input !== $this->input[$parameters[0]]);
	}

	/**
	 * Checks that the field value matches a regex pattern.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateRegex($input, $parameters)
	{
		return (bool) preg_match($parameters[0], $input);
	}

	/**
	 * Checks that the field value is a integer.
	 * 
	 * @access  protected
	 * @param   string     $inut        Field value
	 * @param   array      $parameters  Validator parameters
	 */

	protected function validateInteger($input, $parameters)
	{
		return (bool) preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d+$)/', $input);
	}

	/**
	 * Checks that the field value is a float.
	 * 
	 * @access  protected
	 * @param   string     $inut        Field value
	 * @param   array      $parameters  Validator parameters
	 */

	protected function validateFloat($input, $parameters)
	{
		return (bool) preg_match('/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d+\.\d$)/', $input);
	}

	/**
	 * Checks that the field value is valid HEX.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateHex($input, $parameters)
	{
		return (bool) preg_match('/^[a-f0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value is a valid email address.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateEmail($input, $parameters)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Checks that the field value contains a valid MX reccord.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateEmailDomain($input, $parameters)
	{
		if(empty($input))
		{
			return false;
		}
		
		$email = explode('@', $input);
		
		return checkdnsrr(array_pop($email), 'MX');
	}

	/**
	 * Checks that the field value is an IP address.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateIp($input, $parameters)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_IP);
	}

	/**
	 * Checks that the field value is a valid URL.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateUrl($input, $parameters)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_URL);
	}

	/**
	 * Checks that the field value contains one of the given values.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateIn($input, $parameters)
	{
		return in_array($input, $parameters);
	}

	/**
	 * Checks that the field value does not contain one of the given values.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateNotIn($input, $parameters)
	{
		return ! in_array($input, $parameters);
	}

	/**
	 * Checks that the field value is a valid date.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateDate($input, $parameters)
	{
		return (bool) DateTime::createFromFormat($parameters[0], $input);
	}

	/**
	 * Checks that the field value matches a valid security token.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateToken($input, $parameters)
	{
		return Token::validate($input);
	}

	/**
	 * Registers a custom validator.
	 * 
	 * @access  public
	 * @param   string   $name       Validator name
	 * @param   Closure  $validator  Validator
	 */

	public static function registerValidator($name, Closure $validator)
	{
		static::$validators[String::underscored2camel($name, true)] = $validator;
	}

	/**
	 * Parses the validation rules.
	 * 
	 * @access  protected
	 * @return  array
	 */

	protected function parseRules()
	{
		$rules = array();

		foreach($this->rules as $key => $value)
		{
			foreach(explode('|', trim($value, '|')) as $rule)
			{
				list($validator, $params) = explode(':', $rule, 2) + array(null, null);

				$params = !empty($params) ? str_getcsv($params) : array();

				if($key === '*')
				{
					foreach(array_keys($this->input) as $key)
					{
						$rules[$key][$validator] = $params;
					}
				}
				else
				{
					$rules[$key][$validator] = $params;
				}
			}
		}

		return $rules;
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
		$rules = $this->parseRules();

		foreach($rules as $field => $validators)
		{
			if(in_array($this->input[$field], array('', null, array()), true) && !array_key_exists('required', $validators))
			{
				continue; // Only run validation fields that are required or not empty
			}

			foreach($validators as $validator => $parameters)
			{
				if($this->{'validate' . String::underscored2camel($validator, true)}($this->input[$field], $parameters) === false)
				{
					$this->errors[$field] = 'validate_' . $validator;

					break; // Jump to next field if an error is found
				}
			}
		}

		$errors = $this->errors;

		return empty($this->errors);
	}

	/**
	 * Executes custom validators.
	 * 
	 * @param   string   $method     Method name
	 * @param   array    $arguments  Method arguments
	 * @return  boolean
	 */

	public function __call($name, $arguments)
	{
		throw new \Exception('wtf');
	}
}

/** -------------------- End of file --------------------**/