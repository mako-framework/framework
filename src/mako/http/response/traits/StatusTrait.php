<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response\traits;

/**
 * Status trait.
 *
 * @property int $value
 */
trait StatusTrait
{
	/**
	 * Returns the status code.
	 */
	public function getCode(): int
	{
		return $this->value;
	}

	/**
	 * Returns TRUE if the status is informational and FALSE if not.
	 */
	public function isInformational(): bool
	{
		return $this->value >= 100 && $this->value <= 199;
	}

	/**
	 * Returns TRUE if the status is a success and FALSE if not.
	 */
	public function isSuccess(): bool
	{
		return $this->value >= 200 && $this->value <= 299;
	}

	/**
	 * Returns TRUE if the status is a redirect and FALSE if not.
	 */
	public function isRedirect(): bool
	{
		return $this->value >= 300 && $this->value <= 399;
	}

	/**
	 * Returns TRUE if the status is a client error and FALSE if not.
	 */
	public function isClientError(): bool
	{
		return $this->value >= 400 && $this->value <= 499;
	}

	/**
	 * Returns TRUE if the status is a server error and FALSE if not.
	 */
	public function isServerError(): bool
	{
		return $this->value >= 500 && $this->value <= 599;
	}
}
