<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class User {
	use MarshalTrait;

	/**
	 * @param array<string> $contacts
	 */
	public function __construct(
		#[Field(name: "first-name")] public string $firstName,
		#[Field(name: "last-name")] public string $lastName,
		public int $age,
		public float $height,
		public array $contacts = [],
		public ?string $email = null,
	)
	{
	}

}