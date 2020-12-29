<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\classes\preload;

use mako\classes\preload\PreloaderGenerator;
use mako\tests\TestCase;
use mako\tests\unit\classes\preload\classes\CA;
use mako\tests\unit\classes\preload\classes\CB;
use mako\tests\unit\classes\preload\classes\CC;
use mako\tests\unit\classes\preload\classes\CD;
use mako\tests\unit\classes\preload\classes\CE;

/**
 * @group unit
 */
class PreloaderGeneratorTest extends TestCase
{
	/**
	 *
	 */
	public function testGeneratePreloader(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CA.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([CA::class]));
	}

	/**
	 *
	 */
	public function testGeneratePreloaderFromGenerator(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CA.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader((function()
		{
			yield CA::class;
		})()));
	}

	/**
	 *
	 */
	public function testGeneratePreloaderWithMissingParent(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CA.php',
		  1 => '$classPath/classes/CB.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([CB::class]));
	}

	/**
	 *
	 */
	public function testGeneratePreloaderWithMissingInterface(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CC.php',
		  1 => '$classPath/classes/IA.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([CC::class]));
	}

	/**
	 *
	 */
	public function testGeneratePreloaderWithMissingTrait(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CD.php',
		  1 => '$classPath/classes/TA.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([CD::class]));
	}

	/**
	 *
	 */
	public function testThatOnlyUserGeneratedClassesAndInterfacesGetPreloaded(): void
	{
		$classPath = __DIR__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath/classes/CE.php',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([CE::class]));
	}

}
