<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\cache\stores\traits;

/**
 * Get or else trait.
 *
 * @author  Frederic G. Østby
 */
trait GetOrElseTrait
{
	/**
	 * {@inheritdoc}
	 */
	public function getOrElse(string $key, callable $data, int $ttl = 0)
	{
		if(!$this->has($key))
		{
			$data = $data();

			$this->put($key, $data, $ttl);

			return $data;
		}

		return $this->get($key);
	}
}