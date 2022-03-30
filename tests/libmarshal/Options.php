<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class Options {
	use MarshalTrait;

	public function __construct(
		#[Field]
		protected string $name,
		#[Field]
		protected string $type,
		#[Field]
		protected int $testField,
		protected bool $unmarshaledField
	)
	{
	}
}