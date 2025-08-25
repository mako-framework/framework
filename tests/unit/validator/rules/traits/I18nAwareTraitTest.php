<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\validator\rules\traits;

use mako\i18n\I18n;
use mako\tests\TestCase;
use mako\validator\rules\I18nAwareInterface;
use mako\validator\rules\RuleInterface;
use mako\validator\rules\traits\I18nAwareTrait;
use Mockery;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class I18nAwareTraitTest extends TestCase
{
	/**
	 *
	 */
	public function testCustomErrorMessage(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('validate.overrides.messages.foobar.barfoo', ['foobar'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}

	/**
	 *
	 */
	public function testCustomErrorMessageFromPackage(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('package::validate.overrides.messages.foobar.barfoo')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('package::validate.overrides.messages.foobar.barfoo', ['foobar'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo', 'package'));
	}

	/**
	 *
	 */
	public function testCustomErrorMessageWithParameters(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			protected $foo = 'foovalue';

			protected $bar = 'barvalue';

			protected $i18nParameters = ['foo', 'bar'];

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('validate.overrides.messages.foobar.barfoo', ['foobar', 'foovalue', 'barvalue'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}

	/**
	 *
	 */
	public function testTranslatedFieldName(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.overrides.fieldnames.foobar')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('validate.overrides.fieldnames.foobar')->andReturn('foobaz');

		$i18n->shouldReceive('has')->once()->with('validate.barfoo')->andReturn(true);

		$i18n->shouldReceive('get')->once()->with('validate.barfoo', ['foobaz'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}

	/**
	 *
	 */
	public function testTranslatedFieldNames(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			protected $foo = 'foovalue';

			protected $bar = 'barvalue';

			protected $i18nParameters = ['foo', 'bar'];

			protected $i18nFieldNameParameters = ['foo'];

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.overrides.fieldnames.foobar')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('validate.overrides.fieldnames.foobar')->andReturn('foobaz');

		$i18n->shouldReceive('has')->once()->with('validate.overrides.fieldnames.foovalue')->andReturnTrue();

		$i18n->shouldReceive('get')->once()->with('validate.overrides.fieldnames.foovalue')->andReturn('foofield');

		$i18n->shouldReceive('has')->once()->with('validate.barfoo')->andReturn(true);

		$i18n->shouldReceive('get')->once()->with('validate.barfoo', ['foobaz', 'foofield', 'barvalue'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}

	/**
	 *
	 */
	public function testDefaultI18nMessage(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return '';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.overrides.fieldnames.foobar')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.barfoo')->andReturn(true);

		$i18n->shouldReceive('get')->once()->with('validate.barfoo', ['foobar'])->andReturn('translated');

		$rule->setI18n($i18n);

		$this->assertSame('translated', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}

	/**
	 *
	 */
	public function testDefaultMessage(): void
	{
		$rule = new class implements I18nAwareInterface, RuleInterface {
			use I18nAwareTrait;

			public function validateWhenEmpty(): bool
			{
			return false;
			}

			public function validate(mixed $value, string $field, array $input): bool
			{
			return true;
			}

			public function getErrorMessage(string $field): string
			{
			return 'fallback';
			}
		};

		$i18n = Mockery::mock(I18n::class);

		$i18n->shouldReceive('has')->once()->with('validate.overrides.messages.foobar.barfoo')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.overrides.fieldnames.foobar')->andReturnFalse();

		$i18n->shouldReceive('has')->once()->with('validate.barfoo')->andReturn(false);

		$rule->setI18n($i18n);

		$this->assertSame('fallback', $rule->getTranslatedErrorMessage('foobar', 'barfoo'));
	}
}
