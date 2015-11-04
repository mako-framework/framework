<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\validator;

use DateTime;
use RuntimeException;

use mako\common\FunctionParserTrait;
use mako\i18n\I18n;
use mako\utility\Str;
use mako\utility\UUID;
use mako\validator\plugins\ValidatorPluginInterface;

/**
 * Input validation.
 *
 * @author  Frederic G. Østby
 */

class Validator
{
	use FunctionParserTrait;

	/**
	 * Holds the input data.
	 *
	 * @var array
	 */

	protected $input;

	/**
	 * Holds all the validation rules that we're goind to run.
	 *
	 * @var array
	 */

	protected $rules = [];

	/**
	 * I18n instance.
	 *
	 * @var \mako\i18n\I18n
	 */

	protected $i18n;

	/**
	 * Holds the returned errors.
	 *
	 * @var array
	 */

	protected $errors = [];

	/**
	 * Validator plugins.
	 *
	 * @var array
	 */

	protected $plugins = [];

	/**
	 * Class constructor.
	 *
	 * @access  public
	 * @param   array            $input  Array to validate
	 * @param   array            $rules  Array of validation rules
	 * @param   \mako\i18n\I18n  $i18n   I18n instance
	 */

	public function __construct(array $input, array $rules, I18n $i18n = null)
	{
		$this->input = $input + array_fill_keys(array_keys($rules), null);
		$this->rules = $rules;

		unset($this->input['*']);

		$this->i18n = $i18n;
	}

	/**
	 * Registers a validation plugin.
	 *
	 * @access  public
	 * @param   \mako\validator\plugins\ValidatorPluginInterface  $plugin  Plugin instance
	 */

	public function registerPlugin(ValidatorPluginInterface $plugin)
	{
		$this->plugins[$plugin->getPackageName() . $plugin->getRuleName()] = $plugin;
	}

	/**
	 * Checks that the field isn't empty.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateRequired($input)
	{
		return ! in_array($input, ['', null, []], true);
	}

	/**
	 * Checks that the field value is long enough.
	 *
	 * @access  protected
	 * @param   string     $input      Field value
	 * @param   int        $minLength  Minimum length
	 * @return  boolean
	 */

	protected function validateMinLength($input, $minLength)
	{
		return (mb_strlen($input) >= (int) $minLength);
	}

	/**
	 * Checks that the field value is short enough.
	 *
	 * @access  protected
	 * @param   string     $input      Field value
	 * @param   int        $maxLength  Maximum length
	 * @return  boolean
	 */

	protected function validateMaxLength($input, $maxLength)
	{
		return (mb_strlen($input) <= (int) $maxLength);
	}

	/**
	 * Checks that the field value is of the right length.
	 *
	 * @access  protected
	 * @param   string     $input   Field value
	 * @param   int        $length  Exact length
	 * @return  boolean
	 */

	protected function validateExactLength($input, $length)
	{
		return (mb_strlen($input) === (int) $length);
	}

	/**
	 * Checks that the field value is less than x.
	 *
	 * @access  protected
	 * @param   string     $input     Field value
	 * @param   int        $lessThan  Maximum value + 1
	 * @return  boolean
	 */

	protected function validateLessThan($input, $lessThan)
	{
		return ($input < (int) $lessThan);
	}

	/**
	 * Checks that the field value is less than or equal to x.
	 *
	 * @access  protected
	 * @param   string     $input              Field value
	 * @param   int        $lessThanOrEqualTo  Maximum value
	 * @return  boolean
	 */

	protected function validateLessThanOrEqualTo($input, $lessThanOrEqualTo)
	{
		return ($input <= (int) $lessThanOrEqualTo);
	}

	/**
	 * Checks that the field value is greater than x.
	 *
	 * @access  protected
	 * @param   string     $input        Field value
	 * @param   int        $greaterThan  Minimum value - 1
	 * @return  boolean
	 */

	protected function validateGreaterThan($input, $greaterThan)
	{
		return ($input > (int) $greaterThan);
	}

	/**
	 * Checks that the field value is greater than or equal to x.
	 *
	 * @access  protected
	 * @param   string     $input                 Field value
	 * @param   int        $greaterThanOrEqualTo  Minimum value
	 * @return  boolean
	 */

	protected function validateGreaterThanOrEqualTo($input, $greaterThanOrEqualTo)
	{
		return ($input >= (int) $greaterThanOrEqualTo);
	}

	/**
	 * Checks that the field value is between x and y.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @param   int      $minimum  Minimum value
	 * @param   int      $maximum  Maximum value
	 * @return  boolean
	 */

	protected function validateBetween($input, $minimum, $maximum)
	{
		return ($input >= (int) $minimum && $input <= (int) $maximum);
	}

	/**
	 * Checks that the field value matches the value of another field.
	 *
	 * @access  protected
	 * @param   string     $input      Field value
	 * @param   mixed      $fieldName  Field name
	 * @return  boolean
	 */

	protected function validateMatch($input, $fieldName)
	{
		return ($input === $this->input[$fieldName]);
	}

	/**
	 * Checks that the field value is different from the value of another field.
	 *
	 * @access  protected
	 * @param   string     $input      Field value
	 * @param   mixed      $fieldName  Field name
	 * @return  boolean
	 */

	protected function validateDifferent($input, $fieldName)
	{
		return ($input !== $this->input[$fieldName]);
	}

	/**
	 * Checks that the field value matches a regex pattern.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @param   string     $regex  Regex
	 * @return  boolean
	 */

	protected function validateRegex($input, $regex)
	{
		return (bool) preg_match($regex, $input);
	}

	/**
	 * Checks that the field value is a integer.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateInteger($input)
	{
		return (bool) preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is a float.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateFloat($input)
	{
		return (bool) preg_match('/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d*\.\d+$)/', $input);
	}

	/**
	 * Checks that the field value is a natural.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateNatural($input)
	{
		return (bool) preg_match('/(^0$)|(^[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is a natural non zero.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateNaturalNonZero($input)
	{
		return (bool) preg_match('/(^[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is valid HEX.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateHex($input)
	{
		return (bool) preg_match('/^[a-f0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alpha characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlpha($input)
	{
		return (bool) preg_match('/^[a-z]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alpha unicode characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlphaUnicode($input)
	{
		return (bool) preg_match('/^[\pL]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlphanumeric($input)
	{
		return (bool) preg_match('/^[a-z0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlphanumericUnicode($input)
	{
		return (bool) preg_match('/^[\pL0-9]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric, dash and underscore characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlphaDash($input)
	{
		return (bool) preg_match('/^[a-z0-9_-]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode, dash and underscore characters.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateAlphaDashUnicode($input)
	{
		return (bool) preg_match('/^[\pL0-9_-]+$/u', $input);
	}

	/**
	 * Checks that the field value is a valid email address.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateEmail($input)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Checks that the field value contains a valid MX record.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateEmailDomain($input)
	{
		if(empty($input) || strpos($input, '@') === false)
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
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateIp($input)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_IP);
	}

	/**
	 * Checks that the field value is a valid URL.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateUrl($input)
	{
		return (bool) filter_var($input, FILTER_VALIDATE_URL);
	}

	/**
	 * Checks that the field value contains one of the given values.
	 *
	 * @access  protected
	 * @param   string     $input   Field value
	 * @param   array      $values  Valid values
	 * @return  boolean
	 */

	protected function validateIn($input, $values)
	{
		return in_array($input, $values);
	}

	/**
	 * Checks that the field value does not contain one of the given values.
	 *
	 * @access  protected
	 * @param   string     $input   Field value
	 * @param   array      $values  Invalid values
	 * @return  boolean
	 */

	protected function validateNotIn($input, $values)
	{
		return ! in_array($input, $values);
	}

	/**
	 * Checks that the field value is a valid date.
	 *
	 * @access  protected
	 * @param   string     $input   Field value
	 * @param   array      $format  Date format
	 * @return  boolean
	 */

	protected function validateDate($input, $format)
	{
		return (bool) DateTime::createFromFormat($format, $input);
	}

	/**
	 * Checks that the field value is a valid date before the provided date.
	 *
	 * @access  protected
	 * @param   string     $input   Field valies
	 * @param   string     $format  Date format
	 * @param   string     $date    Date
	 * @return  boolean
	 */

	protected function validateBefore($input, $format, $date)
	{
		if(($input = DateTime::createFromFormat($format, $input)) === false)
		{
			return false;
		}

		return ($input->getTimestamp() < DateTime::createFromFormat($format, $date)->getTimestamp());
	}

	/**
	 * Checks that the field value is a valid date after the provided date.
	 *
	 * @access  protected
	 * @param   string     $input   Field valies
	 * @param   string     $format  Date format
	 * @param   string     $date    Date
	 * @return  boolean
	 */

	protected function validateAfter($input, $format, $date)
	{
		if(($input = DateTime::createFromFormat($format, $input)) === false)
		{
			return false;
		}

		return ($input->getTimestamp() > DateTime::createFromFormat($format, $date)->getTimestamp());
	}

	/**
	 * Checks that the field value is a valid UUID.
	 *
	 * @access  protected
	 * @param   string     $input  Field value
	 * @return  boolean
	 */

	protected function validateUuid($input)
	{
		return UUID::validate($input);
	}

	/**
	 * Parses the validation rules.
	 *
	 * @access  protected
	 * @return  array
	 */

	protected function parseRules()
	{
		$parsedRules = [];

		foreach($this->rules as $key => $rules)
		{
			if(is_string($rules))
			{
				$rules = str_getcsv(trim($rules, '|'), '|');
			}

			foreach($rules as $rule)
			{
				$package = null;

				if(strpos($rule, '::') !== false)
				{
					list($package, $rule) = explode('::', $rule, 2);
				}

				list($rule, $parameters) = $this->parseFunction($rule);

				$validator = ['package' => $package, 'name' => $rule, 'parameters' => $parameters];

				if($key === '*')
				{
					foreach(array_keys($this->input) as $key)
					{
						$parsedRules[$key][$rule] = $validator;
					}
				}
				else
				{
					$parsedRules[$key][$rule] = $validator;
				}
			}
		}

		return $parsedRules;
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

		// We have a i18n instance so we can return a propper error message

		if($this->i18n->has($package . 'validate.overrides.messages.' . $field . '.' . $validator))
		{
			// Return custom field specific error message from the language file

			return $this->i18n->get($package . 'validate.overrides.messages.' . $field . '.' . $validator, array_merge([$field], $parameters));
		}
		else
		{
			// Try to translate field name

			$translateFieldName = function($field) use ($package)
			{
				if($this->i18n->has($package . 'validate.overrides.fieldnames.' . $field))
				{
					$field = $this->i18n->get($package . 'validate.overrides.fieldnames.' . $field);
				}
				else
				{
					$field = str_replace('_', ' ', $field);
				}

				return $field;
			};

			if(in_array($validator, ['match', 'different']))
			{
				$field = [$translateFieldName($field), $translateFieldName(array_shift($parameters))];
			}
			else
			{
				$field = $translateFieldName($field);
			}

			// Return default validation error message from the language file

			return $this->i18n->get($package . 'validate.' . $validator, array_merge((array) $field, $parameters));
		}
	}

	/**
	 * Excecutes the chosen validation rule.
	 *
	 * @access  protected
	 * @param   string     $field      Name of the field that we're validating
	 * @param   array      $validator  Validator
	 * @return  boolean
	 */

	public function validate($field, $validator)
	{
		$parameters = array_merge([$this->input[$field]], $validator['parameters']);

		if(method_exists($this, $rule = 'validate' . Str::underscored2camel($validator['name'])))
		{
			return call_user_func_array([$this, $rule], $parameters);
		}
		elseif(isset($this->plugins[$rule = $validator['package'] . $validator['name']]))
		{
			return call_user_func_array([$this->plugins[$rule], 'validate'], $parameters);
		}
		else
		{
			throw new RuntimeException(vsprintf("%s(): Call to undefined validation rule '%s'.", [__METHOD__, trim($validator['package'] . '::' . $validator['name'], '::')]));
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
			if(in_array($this->input[$field], ['', null, []], true) && !array_key_exists('required', $validators))
			{
				continue; // Only validate fields that are required or not empty
			}

			foreach($validators as $validator)
			{
				if($this->validate($field, $validator) === false)
				{
					$this->errors[$field] = $this->getErrorMessage($field, $validator['package'], $validator['name'], $validator['parameters']);

					break;
				}
			}
		}
	}

	/**
	 * Returns TRUE if all rules passed and FALSE if validation failed.
	 *
	 * @access  public
	 * @param   array    $errors  If $errors is provided, then it is filled with all the error messages
	 * @return  boolean
	 */

	public function isValid(&$errors = null)
	{
		$this->process();

		$errors = $this->errors;

		return empty($this->errors);
	}

	/**
	 * Returns FALSE if all rules passed and TRUE if validation failed.
	 *
	 * @access  public
	 * @param   array    $errors  If $errors is provided, then it is filled with all the error messages
	 * @return  boolean
	 */

	public function isInvalid(&$errors = null)
	{
		$this->process();

		$errors = $this->errors;

		return ! empty($this->errors);
	}

	/**
	 * Returns the validation errors.
	 *
	 * @access  public
	 * @return  array
	 */

	public function getErrors()
	{
		return $this->errors;
	}
}
