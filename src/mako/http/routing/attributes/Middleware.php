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
     * Constructor.
     *
     * @param array|string $middleware Middleware
     */
    public function __construct(
        protected array|string $middleware
    )
    {}

    /**
     * Returns an array of middleware.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return (array) $this->middleware;
    }
}
