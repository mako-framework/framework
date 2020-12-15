<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\tests\unit\preload;

use mako\preload\PreloaderGenerator;
use mako\tests\TestCase;

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
		$classPath = __FILE__;

		$expectedClassLoader = <<<EOF
		<?php

		\$files = array (
		  0 => '$classPath',
		);

		foreach(\$files as \$file)
		{
			opcache_compile_file(\$file);
		}

		EOF;

		$this->assertSame($expectedClassLoader, (new PreloaderGenerator)->generatePreloader([static::class]));
	}
}
