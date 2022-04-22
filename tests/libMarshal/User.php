<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class User {
	use MarshalTrait;

	/**
	 * @param string $firstName
	 * @param string $lastName
	 * @param int $age
	 * @param string[] $contacts
	 * @param string|null $email
	 */
	public function __construct(
		#[Field(name: "first-name")]
		public string $firstName,
		#[Field(name: "last-name")]
		public string $lastName,
		#[Field]
		public int $age,
		#[Field]
		public array $contacts = [],
		#[Field]
		public ?string $email = null,
	)
	{
	}

}