<?php

declare(strict_types=1);

namespace libmarshal;

use libMarshal\attributes\Field;

class User {
	use MarshalTrait;

	public function __construct(
		#[Field(name: "first-name")]
		public string $firstName,
		#[Field(name: "last-name")]
		public string $lastName,
		#[Field]
		public int $age,
		#[Field]
		public string $email
	)
	{
	}

}