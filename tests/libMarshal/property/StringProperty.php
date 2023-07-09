<?php

declare(strict_types=1);

namespace libMarshal\property;

use libMarshal\MarshalTrait;

final class StringProperty {
	use MarshalTrait;

	public function __construct(protected string $value) {
	}
}