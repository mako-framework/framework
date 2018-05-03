<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator;

use mako\i18n\I18n;

use mako\syringe\Container;

use mako\tests\TestCase;

use mako\validator\Validator;

use mako\validator\ValidatorFactory;

use Mockery;

/**
 * @group unit
 */
class ValidatorFactoryTest extends TestCase
{
	/**
	 * Attribute spy.
	 *
	 * @param  \mako\validator\Validator $validator Validator factory
	 * @param  string                    $attribute Attribute name
	 * @return array
	 */
	protected function attributeSpy(Validator $validator, string $attribute): array
	{
		return (function() use ($attribute)
		{
			return $this->$attribute;
		})->bindTo($validator, Validator::class)();
	}

	/**
	 *
	 */
	public function testCreate()
	{
		$i18n = Mockery::mock(I18n::class);

		$container = Mockery::mock(Container::class);

		$factory = new ValidatorFactory($i18n, $container);

		$input = ['foo' => 'bar'];

		$ruleSets = ['foo' => ['required']];

		$validator = $factory->create($input, $ruleSets);

		$this->assertInstanceOf(Validator::class, $validator);

		$this->assertSame($input, $this->attributeSpy($validator, 'input'));

		$this->assertSame($ruleSets, $this->attributeSpy($validator, 'ruleSets'));
	}

	/**
	 *
	 */
	public function testExtend()
	{
		$i18n = Mockery::mock(I18n::class);

		$container = Mockery::mock(Container::class);

		$factory = new ValidatorFactory($i18n, $container);

		$factory->extend('myrule', 'MyRuleClass');

		$validator = $factory->create([], []);

		$rules = $this->attributeSpy($validator, 'rules');

		$this->assertTrue(array_key_exists('myrule', $rules));

		$this->assertSame('MyRuleClass', $rules['myrule']);
	}
}
