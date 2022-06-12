<?php

declare(strict_types=1);

namespace libMarshal\attributes;

use Closure;
use Attribute;
use libMarshal\parser\Parseable;

#[Attribute(Attribute::TARGET_CLASS)]
class Renamer {
    
    /**
     * @var Closure(string): string
     */
    protected Closure $renamer;

    /**
     * @param callable(string): string Renamer function applies on all fields that have an empty string for the name argument in a class.
     */
    public function __construct(
        callable $renamer
    )
    {
        $this->renamer = Closure::fromCallable($renamer);
    }

    /**
     * @return callable(string): string
     */
    public function getRenamer(): callable {
        return $this->renamer;
    }
}