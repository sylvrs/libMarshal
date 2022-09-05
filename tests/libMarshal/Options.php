<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class Options {
	use MarshalTrait;

	public function __construct(
		#[Field]
		public string $name,
		#[Field]
		public string $type,
		#[Field]
		public int $testField,
	)
	{
	}
}