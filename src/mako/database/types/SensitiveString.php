<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\types;

use Override;
use PDO;
use SensitiveParameter;

/**
 * Sensitive string type.
 */
class SensitiveString implements TypeInterface
{
	/**
	 * Constructor.
	 */
	public function __construct(
		#[SensitiveParameter] protected ?string $string
	) {
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getType(): int
	{
		return $this->string === null ? PDO::PARAM_NULL : PDO::PARAM_STR;
	}

	/**
	 * {@inheritDoc}
	 */
	#[Override]
	public function getValue(): ?string
	{
		return $this->string;
	}
}
