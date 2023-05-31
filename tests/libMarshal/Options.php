<?php

declare(strict_types=1);

namespace libMarshal;

class Options {
	use MarshalTrait;

	public function __construct(
		public string $name,
		public string $type,
		public int $testField,
	) {
	}
}