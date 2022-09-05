<?php

declare(strict_types=1);

namespace libMarshal\property;

use libMarshal\attributes\Field;
use libMarshal\MarshalTrait;

final class IntProperty {
	use MarshalTrait;

	public function __construct(
		#[Field] protected int $value
	) {
	}

}