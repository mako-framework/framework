<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\traits;

use mako\syringe\Container;
use mako\tests\TestCase;
use mako\validator\input\HttpInput;
use mako\validator\input\traits\InputValidationTrait;
use mako\validator\ValidationException;
use mako\validator\Validator;
use mako\validator\ValidatorFactory;
use Mockery;

/**
 * @group unit
 */
class InputValidatorTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testWithInputClass(): void
	{
		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andReturn($input);

		//

		$fooInput = Mockery::mock(HttpInput::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput('FooInput'));
	}

	/**
	 *
	 */
	public function testWithInputClassAndAdditionalRules(): void
	{
		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input           = ['username' => 'foobar'];
		$rules           = ['username' => ['required']];
		$additionalRules = ['username' => ['required', 'min_length(4)']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andReturn($input);

		//

		$fooInput = Mockery::mock(HttpInput::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $additionalRules)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput('FooInput', $additionalRules));
	}

	/**
	 *
	 */
	public function testWithInputClassAndExtensions(): void
	{
		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andReturn($input);

		$validator->shouldReceive('extend')->once()->with('rule', 'ruleClass');

		//

		$fooInput = Mockery::mock(HttpInput::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn(['rule' => 'ruleClass']);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput('FooInput'));
	}

	/**
	 *
	 */
	public function testWithInputClassThrowingException(): void
	{
		$this->expectException(ValidationException::class);

		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$exception = Mockery::mock(ValidationException::class);

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andThrow($exception);

		//

		$fooInput = Mockery::mock(HttpInput::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		$fooInput->shouldReceive('getMeta')->andReturn([]);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput('FooInput'));
	}

	/**
	 *
	 */
	public function testWithInputClassThrowingExceptionWithMeta(): void
	{
		$this->expectException(ValidationException::class);

		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$exception = Mockery::mock(ValidationException::class);

		$exception->shouldReceive('addMeta')->once()->with('key', 'value');

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andThrow($exception);

		//

		$fooInput = Mockery::mock(HttpInput::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		$fooInput->shouldReceive('getMeta')->andReturn(['key' => 'value']);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput('FooInput'));
	}

	/**
	 *
	 */
	public function testWithInputArray(): void
	{
		$class = new class
		{
			use InputValidationTrait;

			public function validateInput($input, array $rules = []): array
			{
				return $this->validate($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('validate')->once()->andReturn($input);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules)->andReturn($validator);

		//

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput($input, $rules));
	}
}
