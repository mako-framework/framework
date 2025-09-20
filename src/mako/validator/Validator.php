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
use mako\validator\exceptions\ValidationException;
use mako\validator\exceptions\ValidatorException;
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
use mako\validator\rules\Boolean;
use mako\validator\rules\BooleanFalse;
use mako\validator\rules\BooleanTrue;
use mako\validator\rules\database\Exists;
use mako\validator\rules\database\Unique;
use mako\validator\rules\Date;
use mako\validator\rules\Different;
use mako\validator\rules\Email;
use mako\validator\rules\EmailDomain;
use mako\validator\rules\Enum;
use mako\validator\rules\ExactLength;
use mako\validator\rules\file\Hash;
use mako\validator\rules\file\Hmac;
use mako\validator\rules\file\image\AspectRatio;
use mako\validator\rules\file\image\ExactDimensions;
use mako\validator\rules\file\image\MaxDimensions;
use mako\validator\rules\file\image\MinDimensions;
use mako\validator\rules\file\IsUploaded;
use mako\validator\rules\file\MaxFilenameLength;
use mako\validator\rules\file\MaxFileSize;
use mako\validator\rules\file\MimeType;
use mako\validator\rules\GreaterThan;
use mako\validator\rules\GreaterThanOrEqualTo;
use mako\validator\rules\Hex;
use mako\validator\rules\I18nAwareInterface;
use mako\validator\rules\In;
use mako\validator\rules\IP;
use mako\validator\rules\JSON;
use mako\validator\rules\LessThan;
use mako\validator\rules\LessThanOrEqualTo;
use mako\validator\rules\MatchField;
use mako\validator\rules\MaxLength;
use mako\validator\rules\MinLength;
use mako\validator\rules\NotEmpty;
use mako\validator\rules\NotIn;
use mako\validator\rules\Number;
use mako\validator\rules\NumberFloat;
use mako\validator\rules\NumberInt;
use mako\validator\rules\NumberNatural;
use mako\validator\rules\NumberNaturalNonZero;
use mako\validator\rules\Numeric;
use mako\validator\rules\NumericFloat;
use mako\validator\rules\NumericInt;
use mako\validator\rules\NumericNatural;
use mako\validator\rules\NumericNaturalNonZero;
use mako\validator\rules\Optional;
use mako\validator\rules\Regex;
use mako\validator\rules\Required;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\session\OneTimeToken;
use mako\validator\rules\session\Token;
use mako\validator\rules\Str;
use mako\validator\rules\TimeZone;
use mako\validator\rules\URL;
use mako\validator\rules\UUID;

use function array_fill_keys;
use function array_keys;
use function array_merge_recursive;
use function array_unique;
use function class_exists;
use function compact;
use function in_array;
use function preg_match;
use function sprintf;
use function str_contains;

/**
 * Input validation.
 */
class Validator
{
	use FunctionParserTrait;

	/**
	 * Rule sets.
	 */
	protected array $ruleSets;

	/**
	 * Rules.
	 */
	protected array $rules = [
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
		'boolean:false'            => BooleanFalse::class,
		'boolean:true'             => BooleanTrue::class,
		'boolean'                  => Boolean::class,
		'date'                     => Date::class,
		'different'                => Different::class,
		'email_domain'             => EmailDomain::class,
		'email'                    => Email::class,
		'enum'                     => Enum::class,
		'exact_dimensions'         => ExactDimensions::class,
		'exact_length'             => ExactLength::class,
		'exists'                   => Exists::class,
		'greater_than_or_equal_to' => GreaterThanOrEqualTo::class,
		'greater_than'             => GreaterThan::class,
		'hash'                     => Hash::class,
		'hex'                      => Hex::class,
		'hmac'                     => Hmac::class,
		'in'                       => In::class,
		'ip'                       => IP::class,
		'is_uploaded'              => IsUploaded::class,
		'json'                     => JSON::class,
		'less_than_or_equal_to'    => LessThanOrEqualTo::class,
		'less_than'                => LessThan::class,
		'match'                    => MatchField::class,
		'max_dimensions'           => MaxDimensions::class,
		'max_file_size'            => MaxFileSize::class,
		'max_filename_length'      => MaxFilenameLength::class,
		'max_length'               => MaxLength::class,
		'mime_type'                => MimeType::class,
		'min_dimensions'           => MinDimensions::class,
		'min_length'               => MinLength::class,
		'not_empty'                => NotEmpty::class,
		'not_in'                   => NotIn::class,
		'number:float'             => NumberFloat::class,
		'number:int'               => NumberInt::class,
		'number:natural_non_zero'  => NumberNaturalNonZero::class,
		'number:natural'           => NumberNatural::class,
		'number'                   => Number::class,
		'numeric:float'            => NumericFloat::class,
		'numeric:int'              => NumericInt::class,
		'numeric:natural_non_zero' => NumericNaturalNonZero::class,
		'numeric:natural'          => NumericNatural::class,
		'numeric'                  => Numeric::class,
		'one_time_token'           => OneTimeToken::class,
		'optional'                 => Optional::class,
		'regex'                    => Regex::class,
		'required'                 => Required::class,
		'string'                   => Str::class,
		'time_zone'                => TimeZone::class,
		'token'                    => Token::class,
		'unique'                   => Unique::class,
		'url'                      => URL::class,
		'uuid'                     => UUID::class,
	];

	/**
	 * Original field names.
	 */
	protected array $originalFieldNames = [];

	/**
	 * Is the input valid?
	 */
	protected bool $isValid = true;

	/**
	 * Error messages.
	 */
	protected array $errors = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		protected array $input,
		array $ruleSets = [],
		protected ?I18n $i18n = null,
		protected Container $container = new Container,
		protected bool $validateEmptyFields = false
	) {
		$this->ruleSets = $this->expandFields($ruleSets);
	}

	/**
	 * Registers a custom validation rule.
	 *
	 * @return $this
	 */
	public function extend(string $rule, string $ruleClass): Validator
	{
		$this->rules[$rule] = $ruleClass;

		return $this;
	}

	/**
	 * Returns TRUE if the field name has a wildcard and FALSE if not.
	 */
	protected function hasWilcard(string $string): bool
	{
		return str_contains($string, '*');
	}

	/**
	 * Saves original field name along with the expanded field name.
	 */
	protected function saveOriginalFieldNames(array $fields, string $field): void
	{
		foreach ($fields as $expanded) {
			$this->originalFieldNames[$expanded] = $field;
		}
	}

	/**
	 * Returns the original field name.
	 */
	protected function getOriginalFieldName(string $field): string
	{
		return $this->originalFieldNames[$field] ?? $field;
	}

	/**
	 * Expands fields.
	 */
	protected function expandFields(array $ruleSets): array
	{
		$expanded = [];

		foreach ($ruleSets as $field => $ruleSet) {
			if ($this->hasWilcard($field) === false) {
				$expanded = array_merge_recursive($expanded, [$field => $ruleSet]);

				continue;
			}

			if (!empty($fields = Arr::expandKey($this->input, $field))) {
				if ($field !== '*') {
					$this->saveOriginalFieldNames($fields, $field);
				}

				$fields = array_fill_keys($fields, $ruleSet);
			}

			$expanded = array_merge_recursive($expanded, $fields);
		}

		return $expanded;
	}

	/**
	 * Adds validation rules to input field.
	 *
	 * @return $this
	 */
	public function addRules(string $field, array $ruleSet): Validator
	{
		$this->ruleSets = array_merge_recursive($this->ruleSets, $this->expandFields([$field => $ruleSet]));

		return $this;
	}

	/**
	 * Adds validation rules to input field if the condition is met.
	 *
	 * @return $this
	 */
	public function addRulesIf(string $field, array $ruleSet, bool|Closure $condition): Validator
	{
		if ($condition instanceof Closure) {
			$condition = $condition();
		}

		return $condition ? $this->addRules($field, $ruleSet) : $this;
	}

	/**
	 * Parses the rule.
	 *
	 * @return object{package: string|null, name: string, parameters: array<mixed>}
	 */
	protected function parseRule(string $rule): object
	{
		$package = null;

		if (preg_match('/^([a-z-]+)::(.*)/', $rule, $matches) === 1) {
			$package = $matches[1];
		}

		[$name, $parameters] = $this->parseFunction($rule, false);

		return (object) compact('name', 'parameters', 'package');
	}

	/**
	 * Returns the rule class name.
	 */
	protected function getRuleClassName(string $name): string
	{
		if (isset($this->rules[$name])) {
			return $this->rules[$name];
		}

		if (class_exists($name)) {
			return $name;
		}

		throw new ValidatorException(sprintf('Call to undefined validation rule [ %s ].', $name));
	}

	/**
	 * Creates a rule instance.
	 */
	protected function ruleFactory(string $name, array $parameters): RuleInterface
	{
		return $this->container->get($this->getRuleClassName($name), $parameters);
	}

	/**
	 * Returns TRUE if the input field is considered empty and FALSE if not.
	 */
	protected function isInputFieldEmpty(mixed $value): bool
	{
		return in_array($value, ['', null, []], true);
	}

	/**
	 * Returns the error message.
	 */
	protected function getErrorMessage(RuleInterface $rule, string $field, object $parsedRule): string
	{
		$field = $this->getOriginalFieldName($field);

		if ($this->i18n !== null && $rule instanceof I18nAwareInterface) {
			return $rule->setI18n($this->i18n)->getTranslatedErrorMessage($field, $parsedRule->name, $parsedRule->package);
		}

		return $rule->getErrorMessage($field);
	}

	/**
	 * Validates the field using the specified rule.
	 */
	protected function validateField(string $field, string $rule): bool
	{
		$parsedRule = $this->parseRule($rule);

		$rule = $this->ruleFactory($parsedRule->name, $parsedRule->parameters);

		// Just return true if the input field is empty and the rule doesn't validate empty input

		$inputValue = Arr::get($this->input, $field);

		if (($this->validateEmptyFields === false && $this->isInputFieldEmpty($inputValue) && $rule->validateWhenEmpty() === false)
			|| ($this->validateEmptyFields && Arr::has($this->input, $field) === false)) {
			return true;
		}

		// Validate input

		if ($rule->validate($inputValue, $field, $this->input) === false) {
			$this->errors[$field] = $this->getErrorMessage($rule, $field, $parsedRule);

			return $this->isValid = false;
		}

		return true;
	}

	/**
	 * Processes all validation rules and returns an array containing
	 * the validation status and potential error messages.
	 */
	protected function process(): array
	{
		foreach ($this->ruleSets as $field => $ruleSet) {
			// Ensure that we don't have any duplicated rules for a field

			$ruleSet = array_unique($ruleSet);

			// Validate field and stop as soon as one of the rules fail

			foreach ($ruleSet as $rule) {
				if ($this->validateField($field, $rule) === false) {
					break;
				}
			}
		}

		return [$this->isValid, $this->errors];
	}

	/**
	 * Returns TRUE if all rules passed and FALSE if validation failed.
	 */
	public function isValid(?array &$errors = null): bool
	{
		[$isValid, $errors] = $this->process();

		return $isValid === true;
	}

	/**
	 * Returns false if all rules passed and true if validation failed.
	 */
	public function isInvalid(?array &$errors = null): bool
	{
		[$isValid, $errors] = $this->process();

		return $isValid === false;
	}

	/**
	 * Validates the input and returns an array containing validated data.
	 */
	public function getValidatedInput(): array
	{
		if ($this->isInvalid()) {
			throw new ValidationException($this->errors, 'Invalid input.');
		}

		$validated = [];

		foreach (array_keys($this->ruleSets) as $validatedKey) {
			if (Arr::has($this->input, $validatedKey)) {
				Arr::set($validated, $validatedKey, Arr::get($this->input, $validatedKey));
			}
		}

		return $validated;
	}

	/**
	 * Returns the validation errors.
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}
}
