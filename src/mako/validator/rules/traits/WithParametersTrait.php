<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\validator\rules\traits;

use RuntimeException;

use function array_combine;
use function array_fill;
use function count;
use function vsprintf;

/**
 * With parameters trait.
 *
 * @author Frederic G. Østby
 */
trait WithParametersTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function setParameters(array $parameters): void
	{
		$this->parameters = array_combine($this->parameters, $parameters + array_fill(0, count($this->parameters), null));
	}

	/**
	 * Returns the parameter value.
	 *
	 * @param  string $name     Parameter name
	 * @param  bool   $optional Is the parameter optional?
	 * @return mixed
	 */
	protected function getParameter($name, $optional = false)
	{
		if($optional === false && !isset($this->parameters[$name]))
		{
			throw new RuntimeException(vsprintf('Missing required parameter [ %s ] for validation rule [ %s ].', [$name, static::class]));
		}

		return $this->parameters[$name] ?? null;
	}
}
