<?php

declare(strict_types=1);

namespace sylvrs\libMarshal\attributes;

use Attribute;

/**
 * This attribute is used to exclude a property from being marshaled/unmarshaled.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Exclude {
}