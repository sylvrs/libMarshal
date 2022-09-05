<?php

declare(strict_types=1);

namespace libMarshal\property;

use libMarshal\attributes\Field;

final class StringProperty extends Property {

	public function __construct(
		#[Field] protected string $value
	) {
	}
}