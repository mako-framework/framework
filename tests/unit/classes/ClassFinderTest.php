<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\classes;

use mako\classes\ClassFinder;
use mako\file\Finder;
use mako\tests\TestCase;
use mako\tests\unit\classes\classes\BarClass;
use mako\tests\unit\classes\classes\BazClass;
use mako\tests\unit\classes\classes\FooClass;
use mako\tests\unit\classes\classes\FooInterface;
use mako\tests\unit\classes\classes\FooTrait;
use PHPUnit\Framework\Attributes\Group;

#[Group('unit')]
class ClassFinderTest extends TestCase
{
	/**
	 *
	 */
	public function testClassFinder(): void
	{
		$expectedClasses =
		[
			BarClass::class,
			BazClass::class,
			FooClass::class,
			FooInterface::class,
			FooTrait::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->find());

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderWithExludedClasses(): void
	{
		$expectedClasses =
		[
			FooInterface::class,
			FooTrait::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->excludeClasses()->find());

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderWithExcludedAbstractClasses(): void
	{
		$expectedClasses =
		[
			BarClass::class,
			BazClass::class,
			FooInterface::class,
			FooTrait::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->excludeAbstractClasses()->find());

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderWithExcludedInterfaces(): void
	{
		$expectedClasses =
		[
			BarClass::class,
			BazClass::class,
			FooClass::class,
			FooTrait::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->excludeInterfaces()->find());

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderWithExludedTraits(): void
	{
		$expectedClasses =
		[
			BarClass::class,
			BazClass::class,
			FooClass::class,
			FooInterface::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->excludeTraits()->find());

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderExtending(): void
	{
		$expectedClasses =
		[
			BarClass::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->findExtending(FooClass::class));

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderImplementing(): void
	{
		$expectedClasses =
		[
			BarClass::class,
			FooClass::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->findImplementing(FooInterface::class));

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}

	/**
	 *
	 */
	public function testClassFinderUsing(): void
	{
		$expectedClasses =
		[
			BazClass::class,
		];

		$finder = new ClassFinder(new Finder([__DIR__ . '/classes']));

		$classes = iterator_to_array($finder->findUsing(FooTrait::class));

		sort($expectedClasses);

		sort($classes);

		$this->assertSame($expectedClasses, $classes);
	}
}
