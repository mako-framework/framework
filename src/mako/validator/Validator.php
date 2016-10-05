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
	 * @param   null|string     $input  Field value
	 * @return  bool
	 */
	protected function validateRequired(string $input = null): bool
	{
		return !in_array($input, ['', null, []], true);
	}

	/**
	 * Checks that the field value is long enough.
	 *
	 * @access  protected
	 * @param   null|string  $input      Field value
	 * @param   int          $minLength  Minimum length
	 * @return  bool
	 */
	protected function validateMinLength(string $input = null, int $minLength): bool
	{
		return (mb_strlen($input) >= $minLength);
	}

	/**
	 * Checks that the field value is short enough.
	 *
	 * @access  protected
	 * @param   null|string  $input      Field value
	 * @param   int          $maxLength  Maximum length
	 * @return  bool
	 */
	protected function validateMaxLength(string $input = null, int $maxLength): bool
	{
		return (mb_strlen($input) <= $maxLength);
	}

	/**
	 * Checks that the field value is of the right length.
	 *
	 * @access  protected
	 * @param   null|string  $input   Field value
	 * @param   int          $length  Exact length
	 * @return  bool
	 */
	protected function validateExactLength(string $input = null, int $length): bool
	{
		return (mb_strlen($input) === $length);
	}

	/**
	 * Checks that the field value is less than x.
	 *
	 * @access  protected
	 * @param   null|string  $input     Field value
	 * @param   int          $lessThan  Maximum value + 1
	 * @return  bool
	 */
	protected function validateLessThan(string $input = null, int $lessThan): bool
	{
		return ((int) $input < $lessThan);
	}

	/**
	 * Checks that the field value is less than or equal to x.
	 *
	 * @access  protected
	 * @param   null|string  $input              Field value
	 * @param   int          $lessThanOrEqualTo  Maximum value
	 * @return  bool
	 */
	protected function validateLessThanOrEqualTo(string $input = null, int $lessThanOrEqualTo): bool
	{
		return ((int) $input <= $lessThanOrEqualTo);
	}

	/**
	 * Checks that the field value is greater than x.
	 *
	 * @access  protected
	 * @param   null|string  $input        Field value
	 * @param   int          $greaterThan  Minimum value - 1
	 * @return  bool
	 */
	protected function validateGreaterThan(string $input = null, int $greaterThan): bool
	{
		return ((int) $input > $greaterThan);
	}

	/**
	 * Checks that the field value is greater than or equal to x.
	 *
	 * @access  protected
	 * @param   null|string  $input                 Field value
	 * @param   int          $greaterThanOrEqualTo  Minimum value
	 * @return  bool
	 */
	protected function validateGreaterThanOrEqualTo(string $input = null, int $greaterThanOrEqualTo): bool
	{
		return ((int) $input >= $greaterThanOrEqualTo);
	}

	/**
	 * Checks that the field value is between x and y.
	 *
	 * @access  protected
	 * @param   null|string  $input    Field value
	 * @param   int          $minimum  Minimum value
	 * @param   int          $maximum  Maximum value
	 * @return  bool
	 */
	protected function validateBetween(string $input = null, int $minimum, int $maximum): bool
	{
		return ((int) $input >= $minimum && (int) $input <= $maximum);
	}

	/**
	 * Checks that the field value matches the value of another field.
	 *
	 * @access  protected
	 * @param   null|string  $input      Field value
	 * @param   string       $fieldName  Field name
	 * @return  bool
	 */
	protected function validateMatch(string $input = null, string $fieldName): bool
	{
		return ($input === $this->input[$fieldName]);
	}

	/**
	 * Checks that the field value is different from the value of another field.
	 *
	 * @access  protected
	 * @param   null|string  $input      Field value
	 * @param   string       $fieldName  Field name
	 * @return  bool
	 */
	protected function validateDifferent(string $input = null, string $fieldName): bool
	{
		return ($input !== $this->input[$fieldName]);
	}

	/**
	 * Checks that the field value matches a regex pattern.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @param   string       $regex  Regex
	 * @return  bool
	 */
	protected function validateRegex(string $input = null, string $regex): bool
	{
		return (bool) preg_match($regex, $input);
	}

	/**
	 * Checks that the field value is a integer.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateInteger(string $input = null): bool
	{
		return (bool) preg_match('/(^(\-?)0$)|(^(\-?)[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is a float.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateFloat(string $input = null): bool
	{
		return (bool) preg_match('/(^(\-?)0\.\d+$)|(^(\-?)[1-9]\d*\.\d+$)/', $input);
	}

	/**
	 * Checks that the field value is a natural.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateNatural(string $input = null): bool
	{
		return (bool) preg_match('/(^0$)|(^[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is a natural non zero.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateNaturalNonZero(string $input = null): bool
	{
		return (bool) preg_match('/(^[1-9]\d*$)/', $input);
	}

	/**
	 * Checks that the field value is valid HEX.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateHex(string $input = null): bool
	{
		return (bool) preg_match('/^[a-f0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alpha characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlpha(string $input = null): bool
	{
		return (bool) preg_match('/^[a-z]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alpha unicode characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlphaUnicode(string $input = null): bool
	{
		return (bool) preg_match('/^[\pL]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlphanumeric(string $input = null): bool
	{
		return (bool) preg_match('/^[a-z0-9]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlphanumericUnicode(string $input = null): bool
	{
		return (bool) preg_match('/^[\pL0-9]+$/u', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric, dash and underscore characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlphaDash(string $input = null): bool
	{
		return (bool) preg_match('/^[a-z0-9_-]+$/i', $input);
	}

	/**
	 * Checks that the field value only contains valid alphanumeric unicode, dash and underscore characters.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateAlphaDashUnicode(string $input = null): bool
	{
		return (bool) preg_match('/^[\pL0-9_-]+$/u', $input);
	}

	/**
	 * Checks that the field value is a valid email address.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateEmail(string $input = null): bool
	{
		return (bool) filter_var($input, FILTER_VALIDATE_EMAIL);
	}

	/**
	 * Checks that the field value contains a valid MX record.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateEmailDomain(string $input = null): bool
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
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateIp(string $input = null): bool
	{
		return (bool) filter_var($input, FILTER_VALIDATE_IP);
	}

	/**
	 * Checks that the field value is a valid URL.
	 *
	 * @access  protected
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateUrl(string $input = null): bool
	{
		return (bool) filter_var($input, FILTER_VALIDATE_URL);
	}

	/**
	 * Checks that the field value contains one of the given values.
	 *
	 * @access  protected
	 * @param   null|string  $input   Field value
	 * @param   array        $values  Valid values
	 * @return  bool
	 */
	protected function validateIn(string $input = null, array $values): bool
	{
		return in_array($input, $values);
	}

	/**
	 * Checks that the field value does not contain one of the given values.
	 *
	 * @access  protected
	 * @param   null|string  $input   Field value
	 * @param   array        $values  Invalid values
	 * @return  bool
	 */
	protected function validateNotIn(string $input = null, array $values): bool
	{
		return !in_array($input, $values);
	}

	/**
	 * Checks that the field value is a valid date.
	 *
	 * @access  protected
	 * @param   null|string  $input   Field value
	 * @param   string       $format  Date format
	 * @return  bool
	 */
	protected function validateDate(string $input = null, string $format): bool
	{
		return (bool) DateTime::createFromFormat($format, $input);
	}

	/**
	 * Checks that the field value is a valid date before the provided date.
	 *
	 * @access  protected
	 * @param   null|string  $input   Field valies
	 * @param   string       $format  Date format
	 * @param   string       $date    Date
	 * @return  bool
	 */
	protected function validateBefore(string $input = null, string $format, string $date): bool
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
	 * @param   null|string  $input   Field valies
	 * @param   string       $format  Date format
	 * @param   string       $date    Date
	 * @return  bool
	 */
	protected function validateAfter(string $input = null, string $format, string $date): bool
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
	 * @param   null|string  $input  Field value
	 * @return  bool
	 */
	protected function validateUuid(string $input = null): bool
	{
		return UUID::validate($input);
	}

	/**
	 * Parses the validation rules.
	 *
	 * @access  protected
	 * @return  array
	 */
	protected function parseRules(): array
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
	 * @param   string       $field       Field name
	 * @param   string       $validator   Validator name
	 * @param   array        $parameters  Validator parameters
	 * @param   null|string  $package     Package name
	 * @return  string
	 */
	protected function getErrorMessage(string $field, string $validator, array $parameters, string $package = null): string
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
	 * @return  bool
	 */
	protected function validate(string $field, array $validator): bool
	{
		$parameters = array_merge([$this->input[$field]], $validator['parameters']);

		if(method_exists($this, $rule = 'validate' . Str::underscored2camel($validator['name'])))
		{
			return $this->{$rule}(...$parameters);
		}
		elseif(isset($this->plugins[$rule = $validator['package'] . $validator['name']]))
		{
			return $this->plugins[$rule]->validate(...$parameters);
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
					$this->errors[$field] = $this->getErrorMessage($field, $validator['name'], $validator['parameters'], $validator['package']);

					break;
				}
			}
		}
	}

	/**
	 * Returns TRUE if all rules passed and FALSE if validation failed.
	 *
	 * @access  public
	 * @param   array   $errors  If $errors is provided, then it is filled with all the error messages
	 * @return  bool
	 */
	public function isValid(array &$errors = null): bool
	{
		$this->process();

		$errors = $this->errors;

		return empty($this->errors);
	}

	/**
	 * Returns FALSE if all rules passed and TRUE if validation failed.
	 *
	 * @access  public
	 * @param   array   $errors  If $errors is provided, then it is filled with all the error messages
	 * @return  bool
	 */
	public function isInvalid(array &$errors = null): bool
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
	public function getErrors(): array
	{
		return $this->errors;
	}
}