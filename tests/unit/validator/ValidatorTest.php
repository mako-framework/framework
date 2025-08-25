<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator;

use mako\i18n\I18n;
use mako\tests\TestCase;
use mako\validator\exceptions\ValidationException;
use mako\validator\rules\I18nAwareInterface;
use mako\validator\rules\Rule;
use mako\validator\rules\RuleInterface;
use mako\validator\Validator;
use Mockery;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

use function mako\f;

class MyRule extends Rule implements RuleInterface
{
	public function validate(mixed $value, string $field, array $input): bool
	{
		return true;
	}

	public function getErrorMessage(string $field): string
	{
		return 'invalid input';
	}
}

#[Group('unit')]
class ValidatorTest extends TestCase
{
	/**
	 * Attribute spy.
	 */
	protected function attributeSpy(Validator $validator, string $attribute): array
	{
		return (function () use ($attribute) {
			return $this->{$attribute};
		})->bindTo($validator, Validator::class)();
	}

	/**
	 *
	 */
	public function testExtend(): void
	{
		$validator = new Validator([], []);

		$this->assertFalse(array_key_exists('foobar', $this->attributeSpy($validator, 'rules')));

		$validator->extend('foobar', 'Foobar');

		$this->assertTrue(array_key_exists('foobar', $this->attributeSpy($validator, 'rules')));

		$this->assertSame('Foobar', $this->attributeSpy($validator, 'rules')['foobar']);
	}

	/**
	 *
	 */
	public function testBasicRules(): void
	{
		$ruleSets =
		[
			'foo' => ['required', 'min_length(10)'],
			'bar' => ['min_length(10)'],
		];

		$validator = new Validator([], $ruleSets);

		$this->assertSame($ruleSets, $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testBasicWithoutRules(): void
	{
		$validator = new Validator([]);

		$this->assertSame([], $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testAddRules(): void
	{
		$validator = new Validator([], []);

		$this->assertSame([], $this->attributeSpy($validator, 'ruleSets'));

		$validator->addRules('foo', ['required', 'min_length(10)']);
		$validator->addRules('foo', ['max_length(20)']);

		$this->assertSame(['foo' => ['required', 'min_length(10)', 'max_length(20)']], $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testAddRulesIf(): void
	{
		$validator = new Validator([], []);

		$this->assertSame([], $this->attributeSpy($validator, 'ruleSets'));

		$validator->addRulesIf('foo', ['required', 'min_length(10)'], true);
		$validator->addRulesIf('foo', ['max_length(20)'], function () { return true; });

		$validator->addRulesIf('foo', ['required', 'min_length(10)'], false);
		$validator->addRulesIf('foo', ['max_length(20)'], function () { return false; });

		$this->assertSame(['foo' => ['required', 'min_length(10)', 'max_length(20)']], $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testWildcardEpansion(): void
	{
		$input =
		[
			'users' => [
				['foo' => ''],
				['foo' => ''],
				['foo' => ''],
			],
		];

		$ruleSets =
		[
			'users.*.foo' => ['required'],
		];

		$validator = new Validator($input, $ruleSets);

		$expectedRuleSets =
		[
			'users.0.foo' => ['required'],
			'users.1.foo' => ['required'],
			'users.2.foo' => ['required'],
		];

		$this->assertSame($expectedRuleSets, $this->attributeSpy($validator, 'ruleSets'));

		$expecptedOriginalFieldNames =
		[
			'users.0.foo' => 'users.*.foo',
			'users.1.foo' => 'users.*.foo',
			'users.2.foo' => 'users.*.foo',
		];

		$this->assertSame($expecptedOriginalFieldNames, $this->attributeSpy($validator, 'originalFieldNames'));
	}

	/**
	 *
	 */
	public function testNestedWildcardEpansion(): void
	{
		$input =
		[
			'users' => [
				['foo' => ['bar' => '']],
				['foo' => ['bar' => '']],
				['foo' => ['bar' => '']],
			],
		];

		$ruleSets =
		[
			'users.*.*.*' => ['required'],
		];

		$validator = new Validator($input, $ruleSets);

		$expectedRuleSets =
		[
			'users.0.foo.bar' => ['required'],
			'users.1.foo.bar' => ['required'],
			'users.2.foo.bar' => ['required'],
		];

		$this->assertSame($expectedRuleSets, $this->attributeSpy($validator, 'ruleSets'));

		$expecptedOriginalFieldNames =
		[
			'users.0.foo.bar' => 'users.*.*.*',
			'users.1.foo.bar' => 'users.*.*.*',
			'users.2.foo.bar' => 'users.*.*.*',
		];

		$this->assertSame($expecptedOriginalFieldNames, $this->attributeSpy($validator, 'originalFieldNames'));
	}

	/**
	 *
	 */
	public function testWildcardEpansionWithNoInput(): void
	{
		$ruleSets =
		[
			'users.*.foo' => ['required'],
		];

		$validator = new Validator([], $ruleSets);

		$this->assertSame([], $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testValidateOnEmptyFieldWithRuleThatDoesntValidateEmptyInput(): void
	{
		$input =
		[
			'email' => '',
		];

		$ruleSets =
		[
			'email' => ['email'],
		];

		$validator = new Validator($input, $ruleSets);

		$this->assertTrue($validator->isValid($errors1));

		$this->assertFalse($validator->isInvalid($errors2));

		$this->assertSame([], $validator->getErrors());

		$this->assertSame($errors1, $validator->getErrors());

		$this->assertSame($errors2, $validator->getErrors());
	}

	/**
	 *
	 */
	public function testValidateOnEmptyFieldWithRuleThatValidatesEmptyInput(): void
	{
		$input =
		[
			'email' => '',
		];

		$ruleSets =
		[
			'email' => ['required'],
		];

		$expectedErrors = ['email' => 'The email field is required.'];

		$validator = new Validator($input, $ruleSets);

		$this->assertFalse($validator->isValid($errors1));

		$this->assertTrue($validator->isInvalid($errors2));

		$this->assertSame($expectedErrors, $validator->getErrors());

		$this->assertSame($expectedErrors, $errors1);

		$this->assertSame($expectedErrors, $errors2);
	}

	/**
	 *
	 */
	public function testThatErrorMessagesForExpandedFieldsUseTheOriginalName(): void
	{
		$input =
		[
			'user' => ['email' => ''],
		];

		$ruleSets =
		[
			'user.*' => ['required'],
		];

		$validator = new Validator($input, $ruleSets);

		$this->assertFalse($validator->isValid());

		$this->assertTrue($validator->isInvalid());

		$this->assertSame(['user.email' => 'The user.* field is required.'], $validator->getErrors());
	}

	/**
	 *
	 */
	public function testThatErrorMessagesUseTheOriginalNameWhenAWildcardRuleIsPresent(): void
	{
		$input =
		[
			'username' => 'foo',
		];

		$ruleSets =
		[
			'*'        => ['required'],
			'username' => ['min_length(4)'],
		];

		$validator = new Validator($input, $ruleSets);

		$this->assertFalse($validator->isValid());

		$this->assertTrue($validator->isInvalid());

		$this->assertSame(['username' => 'The value of the username field must be at least 4 characters long.'], $validator->getErrors());
	}

	/**
	 *
	 */
	public function testThatParametersGetPassedToRule(): void
	{
		$input =
		[
			'foo' => '123456',
		];

		$ruleSets =
		[
			'foo' => ['max_length(4)'],
		];

		$validator = new Validator($input, $ruleSets);

		$this->assertFalse($validator->isValid());

		$this->assertTrue($validator->isInvalid());

		$this->assertSame(['foo' => 'The value of the foo field must be at most 4 characters long.'], $validator->getErrors());
	}

	/**
	 *
	 */
	public function testGetErrorMessageWithI18n(): void
	{
		$input =
		[
			'foo' => '123456',
		];

		$ruleSets =
		[
			'foo' => ['bar::baz'],
		];

		$i18n = Mockery::mock(I18n::class);

		/** @var Mockery\MockInterface&Validator $validator */
		$validator = Mockery::mock(Validator::class, [$input, $ruleSets, $i18n])->makePartial();

		$validator->shouldAllowMockingProtectedMethods();

		$rule = new class($this) implements I18nAwareInterface, RuleInterface {
			protected $i18n;

			public function __construct(
				protected $test
			) {
			}

			public function validateWhenEmpty(): bool
			{
				return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
				return false;
			}

			public function getErrorMessage(string $field): string
			{
				return 'invalid input';
			}

			public function setI18n(I18n $i18n): I18nAwareInterface
			{
				$this->i18n = $i18n;

				$this->test->assertInstanceOf(I18n::class, $i18n);

				return $this;
			}

			public function getTranslatedErrorMessage(string $field, string $rule, ?string $package = null): string
			{
				$this->test->assertSame('foo', $field);

				$this->test->assertSame('bar::baz', $rule);

				$this->test->assertSame('bar', $package);

				return 'custom message';
			}
		};

		$validator->shouldReceive('ruleFactory')->once()->with('bar::baz', [])->andReturn($rule);

		$this->assertFalse($validator->isValid());

		$this->assertSame(['foo' => 'custom message'], $validator->getErrors());
	}

	/**
	 *
	 */
	public function testValidate(): void
	{
		$input = ['username' => 'foo', 'password' => 'bar', 'bio' => 'Hello, world!'];

		$rules = ['username' => ['required'], 'password' => ['required']];

		$validator = new Validator($input, $rules);

		$this->assertSame(['username' => 'foo', 'password' => 'bar'], $validator->getValidatedInput());

		//

		$rules = ['username' => ['required'], 'password' => ['required'], 'bio' => ['optional']];

		$validator = new Validator($input, $rules);

		$this->assertSame($input, $validator->getValidatedInput());
	}

	/**
	 *
	 */
	public function testValidateWithError(): void
	{
		$input = ['username' => 'foo'];

		$rules = ['username' => ['required'], 'password' => ['required']];

		$validator = new Validator($input, $rules);

		try {
			$validator->getValidatedInput();
		}
		catch (Throwable|ValidationException $e) {
			$this->assertInstanceOf(ValidationException::class, $e);

			$this->assertSame(['password' => 'The password field is required.'], $e->getErrors());
		}
	}

	/**
	 *
	 */
	public function testValidateWithCustomRuntimeRule(): void
	{
		$input = ['username' => 'foo'];

		$rules = ['username' => [f(MyRule::class)]];

		$validator = new Validator($input, $rules);

		$this->assertSame($input, $validator->getValidatedInput());

		//

		$input = ['username' => 'foo'];

		$rules = ['username' => [MyRule::class]];

		$validator = new Validator($input, $rules);

		$this->assertSame($input, $validator->getValidatedInput());
	}

	/**
	 *
	 */
	public function testForcedValidationOnEmptyFields(): void
	{
		$input = ['username' => ''];

		$rules = ['username' => ['min_length(4)']];

		$validator = new Validator($input, $rules);

		$this->assertSame($input, $validator->getValidatedInput());

		//

		$this->expectException(ValidationException::class);

		$validator = new Validator($input, $rules, validateEmptyFields: true);

		$this->assertSame($input, $validator->getValidatedInput());
	}
}
