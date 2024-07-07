<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\attributes;

use Attribute;

/**
 * Middleware attribute.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Middleware
{
	/**
	 * Parameters.
	 */
	protected array $parameters;

    /**
     * Constructor.
     */
    public function __construct(
        protected string $middleware,
		mixed ...$parameters
    ) {
		$this->parameters = $parameters;
	}

    /**
     * Returns the middleware.
     */
    public function getMiddleware(): string
    {
        return $this->middleware;
    }

	/**
	 * Returns the middleware parameters.
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	/**
	 * Returns the middleware and parameters.
	 *
	 * @return array{middleware: string, parameters: array}
	 */
	public function getMiddlewareAndParameters(): array
	{
		return ['middleware' => $this->middleware, 'parameters' => $this->parameters];
	}
}
