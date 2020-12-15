<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\preload;

use ReflectionClass;

use function array_map;
use function sprintf;
use function var_export;

/**
 * Preloader generator.
 */
class PreloaderGenerator
{
	/**
	 * Preloader template.
	 *
	 * @var string
	 */
	protected $template = <<<'EOF'
	<?php

	$files = %s;

	foreach($files as $file)
	{
		opcache_compile_file($file);
	}

	EOF;

	/**
	 * Returns an array containing the file paths of the provided classes.
	 *
	 * @param  array $classes An array of class names
	 * @return array
	 */
	protected function getClassFilePaths(array $classes): array
	{
		return array_map(function($class)
		{
			return (new ReflectionClass($class))->getFileName();
		}, $classes);
	}

	/**
	 * Generates a preloader.
	 *
	 * @param  array  $classes An array of class names
	 * @return string
	 */
	public function generatePreloader(array $classes): string
	{
		return sprintf($this->template, var_export($this->getClassFilePaths($classes), true));
	}
}
