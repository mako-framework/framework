<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\routing\attributes;

use Attribute;

/**
 * Constraint attribute.
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Constraint
{
    /**
     * Constructor.
     *
     * @param array|string $constraint Constraint
     */
    public function __construct(
        protected array|string $constraint
    )
    {}

    /**
     * Returns an array of constraints.
     *
     * @return array
     */
    public function getConstraints(): array
    {
        return (array) $this->constraint;
    }
}
