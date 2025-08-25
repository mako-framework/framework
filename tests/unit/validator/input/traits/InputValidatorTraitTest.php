<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\input\traits;

use mako\syringe\Container;
use mako\tests\TestCase;
use mako\validator\exceptions\ValidationException;
use mako\validator\input\http\Input;
use mako\validator\input\traits\InputValidationTrait;
use mako\validator\Validator;
use mako\validator\ValidatorFactory;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class InputValidatorTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testWithInputClass(): void
	{
		$class = new class {
			use InputValidationTrait;

			public $container;
			public $validator;

			public function validateInput($input, array $rules = []): array
			{
				return $this->getValidatedInput($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('getValidatedInput')->once()->andReturn($input);

		//

		$fooInput = Mockery::mock(Input::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules, false)->andReturn($validator);

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
		$class = new class {
			use InputValidationTrait;

			public $container;
			public $validator;

			public function validateInput($input, array $rules = []): array
			{
				return $this->getValidatedInput($input, $rules);
			}
		};

		$input           = ['username' => 'foobar'];
		$rules           = ['username' => ['required']];
		$additionalRules = ['username' => ['required', 'min_length(4)']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('getValidatedInput')->once()->andReturn($input);

		//

		$fooInput = Mockery::mock(Input::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $additionalRules, false)->andReturn($validator);

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
		$class = new class {
			use InputValidationTrait;

			public $container;
			public $validator;

			public function validateInput($input, array $rules = []): array
			{
				return $this->getValidatedInput($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('getValidatedInput')->once()->andReturn($input);

		$validator->shouldReceive('extend')->once()->with('rule', 'ruleClass');

		//

		$fooInput = Mockery::mock(Input::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn(['rule' => 'ruleClass']);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules, false)->andReturn($validator);

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

		$class = new class {
			use InputValidationTrait;

			public $container;
			public $validator;

			public function validateInput($input, array $rules = []): array
			{
				return $this->getValidatedInput($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$exception = Mockery::mock(ValidationException::class);

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('getValidatedInput')->once()->andThrow($exception);

		//

		$fooInput = Mockery::mock(Input::class);

		$fooInput->shouldReceive('getInput')->once()->andReturn($input);

		$fooInput->shouldReceive('getRules')->once()->andReturn($rules);

		$fooInput->shouldReceive('getExtensions')->once()->andReturn([]);

		$fooInput->shouldReceive('addConditionalRules')->once()->with($validator);

		//

		$container = Mockery::mock(Container::class);

		$container->shouldReceive('get')->once()->with('FooInput')->andReturn($fooInput);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules, false)->andReturn($validator);

		//

		$class->container = $container;

		$class->validator = $validatorFactory;

		//

		$exception->shouldReceive('setInput')->once()->with($fooInput);

		//

		$this->assertSame($input, $class->validateInput('FooInput'));
	}

	/**
	 *
	 */
	public function testWithInputArray(): void
	{
		$class = new class {
			use InputValidationTrait;

			public $validator;

			public function validateInput($input, array $rules = []): array
			{
				return $this->getValidatedInput($input, $rules);
			}
		};

		$input = ['username' => 'foobar'];
		$rules = ['username' => ['required']];

		//

		$validator = Mockery::mock(Validator::class);

		$validator->shouldReceive('getValidatedInput')->once()->andReturn($input);

		//

		$validatorFactory = Mockery::mock(ValidatorFactory::class);

		$validatorFactory->shouldReceive('create')->once()->with($input, $rules, false)->andReturn($validator);

		//

		$class->validator = $validatorFactory;

		//

		$this->assertSame($input, $class->validateInput($input, $rules));
	}
}
