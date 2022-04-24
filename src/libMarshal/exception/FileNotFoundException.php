<?php

declare(strict_types=1);

namespace libMarshal\exception;

use RuntimeException;

/**
 * An exception that will be thrown when a file is not found.
 *
 * This is primarily used for `loadFromJson()` and `loadFromYaml()`.
 */
class FileNotFoundException extends RuntimeException {
}