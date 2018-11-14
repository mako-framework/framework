<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator;

use Closure;
use mako\common\traits\FunctionParserTrait;
use mako\i18n\I18n;
use mako\syringe\Container;
use mako\utility\Arr;
use mako\validator\rules\After;
use mako\validator\rules\Alpha;
use mako\validator\rules\Alphanumeric;
use mako\validator\rules\AlphanumericDash;
use mako\validator\rules\AlphanumericDashUnicode;
use mako\validator\rules\AlphanumericUnicode;
use mako\validator\rules\AlphaUnicode;
use mako\validator\rules\Arr as ArrRule;
use mako\validator\rules\Before;
use mako\validator\rules\Between;
use mako\validator\rules\database\Exists;
use mako\validator\rules\database\Unique;
use mako\validator\rules\Date;
use mako\validator\rules\Different;
use mako\validator\rules\Email;
use mako\validator\rules\EmailDomain;
use mako\validator\rules\ExactLength;
use mako\validator\rules\file\Hash;
use mako\validator\rules\file\Hmac;
use mako\validator\rules\file\image\AspectRatio;
use mako\validator\rules\file\image\ExactDimensions;
use mako\validator\rules\file\image\MaxDimensions;
use mako\validator\rules\file\image\MinDimensions;
use mako\validator\rules\file\IsUploaded;
use mako\validator\rules\file\MaxFilesize;
use mako\validator\rules\file\Mimetype;
use mako\validator\rules\FloatingPoint;
use mako\validator\rules\GreaterThan;
use mako\validator\rules\GreaterThanOrEqualTo;
use mako\validator\rules\Hex;
use mako\validator\rules\I18nAwareInterface;
use mako\validator\rules\In;
use mako\validator\rules\Integer;
use mako\validator\rules\IP;
use mako\validator\rules\JSON;
use mako\validator\rules\LessThan;
use mako\validator\rules\LessThanOrEqualTo;
use mako\validator\rules\Match;
use mako\validator\rules\MaxLength;
use mako\validator\rules\MinLength;
use mako\validator\rules\Natural;
use mako\validator\rules\NaturalNonZero;
use mako\validator\rules\NotIn;
use mako\validator\rules\Regex;
use mako\validator\rules\Required;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\session\OneTimeToken;
use mako\validator\rules\session\Token;
use mako\validator\rules\URL;
use mako\validator\rules\UUID;
use mako\validator\rules\WithParametersInterface;
use RuntimeException;

use function array_fill_keys;
use function array_merge_recursive;
use function array_unique;
use function compact;
use function in_array;
use function json_encode;
use function preg_match;
use function strpos;
use function substr;
use function vsprintf;

/**
 * Input validation.
 *
 * @author Frederic G. Østby
 */
class Validator
{
	use FunctionParserTrait;

	/**
	 * Input.
	 *
	 * @var array
	 */
	protected $input;

	/**
	 * Rule sets.
	 *
	 * @var array
	 */
	protected $ruleSets;

	/**
	 * I18n.
	 *
	 * @var \mako\i18n\I18n
	 */
	protected $i18n;

	/**
	 * Container.
	 *
	 * @var \mako\syringe\Container
	 */
	protected $container;

	/**
	 * Rules.
	 *
	 * @var array
	 */
	protected $rules =
	[
		'after'                    => After::class,
		'alpha_dash_unicode'       => AlphanumericDashUnicode::class,
		'alpha_dash'               => AlphanumericDash::class,
		'alpha_unicode'            => AlphaUnicode::class,
		'alpha'                    => Alpha::class,
		'alphanumeric_unicode'     => AlphanumericUnicode::class,
		'alphanumeric'             => Alphanumeric::class,
		'array'                    => ArrRule::class,
		'aspect_ratio'             => AspectRatio::class,
		'before'                   => Before::class,
		'between'                  => Between::class,
		'date'                     => Date::class,
		'different'                => Different::class,
		'email_domain'             => EmailDomain::class,
		'email'                    => Email::class,
		'exact_dimensions'         => ExactDimensions::class,
		'exact_length'             => ExactLength::class,
		'exists'                   => Exists::class,
		'float'                    => FloatingPoint::class,
		'greater_than_or_equal_to' => GreaterThanOrEqualTo::class,
		'greater_than'             => GreaterThan::class,
		'hash'                     => Hash::class,
		'hex'                      => Hex::class,
		'hmac'                     => Hmac::class,
		'in'                       => In::class,
		'integer'                  => Integer::class,
		'ip'                       => IP::class,
		'is_uploaded'              => IsUploaded::class,
		'json'                     => JSON::class,
		'less_than_or_equal_to'    => LessThanOrEqualTo::class,
		'less_than'                => LessThan::class,
		'match'                    => Match::class,
		'max_dimensions'           => MaxDimensions::class,
		'max_filesize'             => MaxFilesize::class,
		'max_length'               => MaxLength::class,
		'mimetype'                 => Mimetype::class,
		'min_dimensions'           => MinDimensions::class,
		'min_length'               => MinLength::class,
		'natural_non_zero'         => NaturalNonZero::class,
		'natural'                  => Natural::class,
		'not_in'                   => NotIn::class,
		'one_time_token'           => OneTimeToken::class,
		'regex'                    => Regex::class,
		'required'                 => Required::class,
		'token'                    => Token::class,
		'unique'                   => Unique::class,
		'url'                      => URL::class,
		'uuid'                     => UUID::class,
	];

	/**
	 * Original field names.
	 *
	 * @var array
	 */
	protected $originalFieldNames;

	/**
	 * Is the input valid?
	 *
	 * @var bool
	 */
	protected $isValid = true;

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Constructor.
	 *
	 * @param array                        $input     Input
	 * @param array                        $ruleSets  Rule sets
	 * @param \mako\i18n\I18n|null         $i18n      I18n
	 * @param \mako\syringe\Container|null $container Container
	 */
	public function __construct(array $input, array $ruleSets, I18n $i18n = null, Container $container = null)
	{
		$this->input = $input;

		$this->ruleSets = $this->expandFields($ruleSets);

		$this->i18n = $i18n;

		$this->container = $container ?? new Container;
	}

	/**
	 * Rule builder.
	 *
	 * @param  string $ruleName      Rule name
	 * @param  mixed  ...$parameters Rule parameters
	 * @return string
	 */
	public static function rule(string $ruleName, ...$parameters): string
	{
		if(empty($parameters))
		{
			return $ruleName;
		}

		return $ruleName . '(' . substr(json_encode($parameters), 1, -1) . ')';
	}

	/**
	 * Registers a custom validation rule.
	 *
	 * @param  string                    $rule      Rule
	 * @param  string                    $ruleClass Rule class
	 * @return \mako\validator\Validator
	 */
	public function extend(string $rule, string $ruleClass): Validator
	{
		$this->rules[$rule] = $ruleClass;

		return $this;
	}

	/**
	 * Returns true if the field name has a wildcard and false if not.
	 *
	 * @param  string $string Field name
	 * @return bool
	 */
	protected function hasWilcard(string $string): bool
	{
		return strpos($string, '*') !== false;
	}

	/**
	 * Saves original field name along with the expanded field name.
	 *
	 * @param array  $fields Expanded field names
	 * @param string $field  Original field name
	 */
	protected function saveOriginalFieldNames(array $fields, string $field)
	{
		foreach($fields as $expanded)
		{
			$this->originalFieldNames[$expanded] = $field;
		}
	}

	/**
	 * Returns the original field name.
	 *
	 * @param  string $field Field name
	 * @return string
	 */
	protected function getOriginalFieldName(string $field): string
	{
		return $this->originalFieldNames[$field] ?? $field;
	}

	/**
	 * Expands fields.
	 *
	 * @param  array $ruleSets Rule sets
	 * @return array
	 */
	protected function expandFields(array $ruleSets): array
	{
		$expanded = [];

		foreach($ruleSets as $field => $ruleSet)
		{
			if($this->hasWilcard($field) === false)
			{
				$expanded = array_merge_recursive($expanded, [$field => $ruleSet]);

				continue;
			}

			if(!empty($fields = Arr::expandKey($this->input, $field)))
			{
				$this->saveOriginalFieldNames($fields, $field);

				$fields = array_fill_keys($fields, $ruleSet);
			}

			$expanded = array_merge_recursive($expanded, $fields);
		}

		return $expanded;
	}

	/**
	 * Adds validation rules to input field.
	 *
	 * @param  string                    $field   Input field
	 * @param  array                     $ruleSet Rule set
	 * @return \mako\validator\Validator
	 */
	public function addRules(string $field, array $ruleSet): Validator
	{
		$this->ruleSets = array_merge_recursive($this->ruleSets, $this->expandFields([$field => $ruleSet]));

		return $this;
	}

	/**
	 * Adds validation rules to input field if the condition is met.
	 *
	 * @param  string                    $field     Input field
	 * @param  array                     $ruleSet   Rule set
	 * @param  bool|\Closure             $condition Condition
	 * @return \mako\validator\Validator
	 */
	public function addRulesIf(string $field, array $ruleSet, $condition): Validator
	{
		if($condition instanceof Closure)
		{
			$condition = $condition();
		}

		return $condition ? $this->addRules($field, $ruleSet) : $this;
	}

	/**
	 * Parses the rule.
	 *
	 * @param  string $rule Rule
	 * @return object
	 */
	protected function parseRule(string $rule)
	{
		$package = null;

		if(preg_match('/^([a-z-]+)::(.*)/', $rule, $matches) === 1)
		{
			$package = $matches[1];
		}

		list($name, $parameters) = $this->parseFunction($rule, false);

		return (object) compact('name', 'parameters', 'package');
	}

	/**
	 * Returns the rule class name.
	 *
	 * @param  string $name Rule name
	 * @return string
	 */
	protected function getRuleClassName(string $name): string
	{
		if(!isset($this->rules[$name]))
		{
			throw new RuntimeException(vsprintf('Call to undefined validation rule [ %s ].', [$name]));
		}

		return $this->rules[$name];
	}

	/**
	 * Creates a rule instance.
	 *
	 * @param  string                              $name Rule name
	 * @return \mako\validator\rules\RuleInterface
	 */
	protected function ruleFactory(string $name): RuleInterface
	{
		return $this->container->get($this->getRuleClassName($name));
	}

	/**
	 * Returns true if the input field is considered empty and false if not.
	 *
	 * @param  mixed $value Value
	 * @return bool
	 */
	protected function isInputFieldEmpty($value): bool
	{
		return in_array($value, ['', null, []], true);
	}

	/**
	 * Returns the error message.
	 *
	 * @param  \mako\validator\rules\RuleInterface $rule       Rule
	 * @param  string                              $field      Field name
	 * @param  object                              $parsedRule Parsed rule
	 * @return string
	 */
	protected function getErrorMessage(RuleInterface $rule, $field, $parsedRule): string
	{
		$field = $this->getOriginalFieldName($field);

		if($this->i18n !== null && $rule instanceof I18nAwareInterface)
		{
			return $rule->setI18n($this->i18n)->getTranslatedErrorMessage($field, $parsedRule->name, $parsedRule->package);
		}

		return $rule->getErrorMessage($field);
	}

	/**
	 * Validates the field using the specified rule.
	 *
	 * @param  string $field Field name
	 * @param  string $rule  Rule
	 * @return bool
	 */
	protected function validate(string $field, string $rule): bool
	{
		$parsedRule = $this->parseRule($rule);

		$rule = $this->ruleFactory($parsedRule->name);

		// Just return true if the input field is empty and the rule doesn't validate empty input

		if($this->isInputFieldEmpty($inputValue = Arr::get($this->input, $field)) && $rule->validateWhenEmpty() === false)
		{
			return true;
		}

		// Set parameters if the rule requires it

		if($rule instanceof WithParametersInterface)
		{
			$rule->setParameters($parsedRule->parameters);
		}

		// Validate input

		if($rule->validate($inputValue, $this->input) === false)
		{
			$this->errors[$field] = $this->getErrorMessage($rule, $field, $parsedRule);

			return $this->isValid = false;
		}

		return true;
	}

	/**
	 * Processes all validation rules and returns an array containing
	 * the validation status and potential error messages.
	 *
	 * @return array
	 */
	protected function process(): array
	{
		foreach($this->ruleSets as $field => $ruleSet)
		{
			// Ensure that we don't have any duplicated rules for a field

			$ruleSet = array_unique($ruleSet);

			// Validate field and stop as soon as one of the rules fail

			foreach($ruleSet as $rule)
			{
				if($this->validate($field, $rule) === false)
				{
					break;
				}
			}
		}

		return [$this->isValid, $this->errors];
	}

	/**
	 * Returns true if all rules passed and false if validation failed.
	 *
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isValid(array &$errors = null): bool
	{
		list($isValid, $errors) = $this->process();

		return $isValid === true;
	}

	/**
	 * Returns false if all rules passed and true if validation failed.
	 *
	 * @param  array|null &$errors If $errors is provided, then it is filled with all the error messages
	 * @return bool
	 */
	public function isInvalid(array &$errors = null): bool
	{
		list($isValid, $errors) = $this->process();

		return $isValid === false;
	}

	/**
	 * Returns the validation errors.
	 *
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
