<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\cli\input\helpers;

use function array_keys;
use function implode;
use function mb_strtolower;
use function mb_strtoupper;
use function trim;

/**
 * Confirmation helper.
 */
class Confirmation extends Question
{
	/**
	 * Returns an array where all array keys lower case.
	 *
	 * @param  array $array Array
	 * @return array
	 */
	protected function normalizeKeys(array $array): array
	{
		$normalized = [];

		foreach($array as $key => $value)
		{
			$normalized[mb_strtolower($key)] = $value;
		}

		return $normalized;
	}

	/**
	 * Returns a slash-separated list of valid options where the default one is highlighted as upper-case.
	 *
	 * @param  array  $options Answer options
	 * @param  string $default Default answer
	 * @return string
	 */
	protected function getOptions(array $options, string $default): string
	{
		$highlighted = [];

		foreach(array_keys($options) as $option)
		{
			$highlighted[] = $option === $default ? mb_strtoupper($option) : $option;
		}

		return implode('/', $highlighted);
	}

	/**
	 * Asks user for confirmation and returns value corresponding to the chosen value.
	 *
	 * @param  string     $question Question to ask
	 * @param  string     $default  Default answer
	 * @param  array|null $options  Answer options
	 * @return bool
	 */
	public function ask(string $question, $default = 'n', ?array $options = null): mixed
	{
		$options = $options === null ? ['y' => true, 'n' => false] : $this->normalizeKeys($options);

		$input = parent::ask(trim($question) . " [{$this->getOptions($options, $default)}] ");

		$input = mb_strtolower(empty($input) ? $default : $input);

		if(!isset($options[$input]))
		{
			return $this->ask($question, $default, $options);
		}

		return $options[$input];
	}
}
