<?php

namespace mako;

use \mako\I18n;
use \mako\UUID;
use \mako\String;
use \mako\Database;
use \mako\security\Token;
use \Closure;
use \DateTime;
use \BadMethodCallException;

/**
 * Input validation.
 *
 * @author     Frederic G. Østby
 * @copyright  (c) 2008-2013 Frederic G. Østby
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
	 * Holds all the validation functions that need to be run.
	 *
	 * @var array
	 */

	protected $rules = array();

	/**
	 * Holds the returned errors.
	 *
	 * @var array
	 */

	protected $errors = array();

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
		$this->input = $input + array_fill_keys(array_keys($rules), null);
		$this->rules = $rules;

		unset($this->input['*']);
	}

	/**
	 * Factory method making method chaining possible right off the bat.
	 *
	 * @access  public
	 * @param   array           $input  Array to validate
	 * @param   array           $rules  Array of validation rules
	 * @return  \mako\Validate
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
		return ($input < $parameters[0]);
	}

	/**
	 * Checks that the field value is less than or equal to x.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateLessThanOrEqualTo($input, $parameters)
	{
		return ($input <= $parameters[0]);
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
		return ($input > $parameters[0]);
	}

	/**
	 * Checks that the field value is greater than or equal to x.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateGreaterThanOrEqualTo($input, $parameters)
	{
		return ($input >= $parameters[0]);
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
		return ($input >= $parameters[0] && $input <= $parameters[1]);
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
		return (bool) preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $input);
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
		return (bool) preg_match('/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d*\.\d+$)/', $input);
	}

	/**
	 * Checks that the field value is a natural.
	 * 
	 * @access  protected
	 * @param   string     $inut        Field value
	 * @param   array      $parameters  Validator parameters
	 */

	protected function validateNatural($input, $parameters)
	{
		return (bool) preg_match('/(^0$)|(^[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is a natural non zero.
	 * 
	 * @access  protected
	 * @param   string     $inut        Field value
	 * @param   array      $parameters  Validator parameters
	 */

	protected function validateNaturalNonZero($input, $parameters)
	{
		return (bool) preg_match('/(^[1-9]\d*$)/', $input);
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
	 * Checks that the field value only contains valid alpha characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlpha($input, $parameters)
	{
		return (bool) preg_match('/^[a-z]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alpha unicode characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlphaUnicode($input, $parameters)
	{
		return (bool) preg_match('/^[\pL]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlphanumeric($input, $parameters)
	{
		return (bool) preg_match('/^[a-z0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlphanumericUnicode($input, $parameters)
	{
		return (bool) preg_match('/^[\pL0-9]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric, dash and underscore characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlphaDash($input, $parameters)
	{
		return (bool) preg_match('/^[a-z0-9_-]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode, dash and underscore characters.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAlphaDashUnicode($input, $parameters)
	{
		return (bool) preg_match('/^[\pL0-9_-]+$/u', $input);
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
	 * Checks that the field value contains a valid MX record.
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
	 * Checks that the field value is a valid date before the provided date.
	 * 
	 * @access  protected
	 * @param   string     $input       Field valies
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateBefore($input, $parameters)
	{
		if(($date = DateTime::createFromFormat($parameters[0], $input)) === false)
		{
			return false;
		}

		return ($date->getTimestamp() < DateTime::createFromFormat($parameters[0], $parameters[1])->getTimestamp());
	}

	/**
	 * Checks that the field value is a valid date after the provided date.
	 * 
	 * @access  protected
	 * @param   string     $input       Field valies
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateAfter($input, $parameters)
	{
		if(($date = DateTime::createFromFormat($parameters[0], $input)) === false)
		{
			return false;
		}

		return ($date->getTimestamp() > DateTime::createFromFormat($parameters[0], $parameters[1])->getTimestamp());
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
	 * Checks that the field value is a valid UUID.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateUuid($input, $parameters)
	{
		return UUID::validate($input);
	}

	/**
	 * Checks that the field value doesn't exist in the database.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateUnique($input, $parameters)
	{
		return (Database::connection()->table($parameters[0])->where($parameters[1], '=', $input)->count() == 0);
	}

	/**
	 * Checks that the field value exist in the database.
	 * 
	 * @access  protected
	 * @param   string     $input       Field value
	 * @param   array      $parameters  Validator parameters
	 * @return  boolean
	 */

	protected function validateExists($input, $parameters)
	{
		return (Database::connection()->table($parameters[0])->where($parameters[1], '=', $input)->count() != 0);
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
		$name = str_replace('::', '_', $name);

		static::$validators['validate' . String::underscored2camel($name, true)] = $validator;
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
			foreach(str_getcsv(trim($value, '|'), '|') as $rule)
			{
				$package = null;

				if(preg_match('/^([0-9a-z_]+::)(.*)$/i', $rule, $matches) !== 0)
				{
					$package = substr($matches[1], 0, -2);

					$rule = $matches[2];
				}

				list($validator, $params) = explode(':', $rule, 2) + array(null, null);

				$rule = array
				(
					'package'    => $package,
					'name'       => $validator,
					'parameters' => !empty($params) ? str_getcsv($params) : array(),
				);

				if($key === '*')
				{
					foreach(array_keys($this->input) as $key)
					{
						$rules[$key][$validator] = $rule;
					}
				}
				else
				{
					$rules[$key][$validator] = $rule;
				}
			}
		}

		return $rules;
	}

	/**
	 * Returns the error message.
	 * 
	 * @access  protected
	 * @param   string     $field       Field name
	 * @param   string     $package     Package name
	 * @param   string     $validator   Validator name
	 * @param   array      $parameters  Validator parameters
	 * @return  string
	 */

	protected function getErrorMessage($field, $package, $validator, $parameters)
	{
		$package = empty($package) ? '' : $package . '::';

		if(I18n::has($package . 'validate.overrides.messages.' . $field . '.' . $validator))
		{
			// Return custom field specific error message from the language file

			return I18n::get($package . 'validate.overrides.messages.' . $field . '.' . $validator, array_merge(array($field), $parameters));
		}
		else
		{
			// Try to translate field name

			$translateFieldName = function($field) use ($package)
			{
				if(I18n::has($package . 'validate.overrides.fieldnames.' . $field))
				{
					$field = I18n::get($package . 'validate.overrides.fieldnames.' . $field);
				}
				else
				{
					$field = str_replace('_', ' ', $field);
				}

				return $field;
			};

			if(in_array($validator, array('match', 'different')))
			{
				$field = array($translateFieldName($field), $translateFieldName(array_shift($parameters)));
			}
			else
			{
				$field = $translateFieldName($field);
			}

			// Return default validation error message from the language file

			return I18n::get($package . 'validate.' . $validator, array_merge((array) $field, $parameters));
		}
	}

	/**
	 * Runs all validation rules.
	 *
	 * @access  public
	 */

	protected function process()
	{
		foreach($this->parseRules() as $field => $validators)
		{
			if(in_array($this->input[$field], array('', null, array()), true) && !array_key_exists('required', $validators))
			{
				continue; // Only validate fields that are required or not empty
			}

			foreach($validators as $validator)
			{
				$package = empty($validator['package']) ? '' : $validator['package'] . '_';

				if($this->{'validate' . String::underscored2camel($package . $validator['name'], true)}($this->input[$field], $validator['parameters']) === false)
				{
					$this->errors[$field] = $this->getErrorMessage($field, $validator['package'], $validator['name'], $validator['parameters']);

					break; // Jump to next field if an error is found
				}
			}
		}
	}

	/**
	 * Returns TRUE if all rules passed and FALSE if validation failed.
	 *
	 * @access  public
	 * @param   array    $errors  (optional) If $errors is provided, then it is filled with all the error messages
	 * @return  boolean
	 */

	public function successful(&$errors = null)
	{
		$this->process();

		$errors = $this->errors;

		return empty($this->errors);
	}

	/**
	 * Returns FALSE if all rules passed and TRUE if validation failed.
	 *
	 * @access  public
	 * @param   array    $errors  (optional) If $errors is provided, then it is filled with all the error messages
	 * @return  boolean
	 */

	public function failed(&$errors = null)
	{
		$this->process();

		$errors = $this->errors;

		return !empty($this->errors);
	}

	/**
	 * Returns the validation errors.
	 * 
	 * @access  public
	 * @return  array
	 */

	public function errors()
	{
		return $this->errors;
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
		if(!isset(static::$validators[$name]))
		{
			throw new BadMethodCallException(vsprintf("Call to undefined method %s::%s().", array(__CLASS__, $name)));
		}

		return call_user_func_array(static::$validators[$name], $arguments);
	}
}

/** -------------------- End of file -------------------- **/