<?php

declare(strict_types=1);

namespace sylvrs\libMarshal;

use sylvrs\libMarshal\attributes\Field;

final class UserWithUninitializedField extends User {
	use MarshalTrait;

	public function __construct(
		string $firstName,
		string $lastName,
		UserRole $role,
		int $age,
		float $height,
		array $contacts,
		string $email,
		#[Field(name: "uninitialized-field", allowUninitialized: true)]
		public int $uninitializedField,
	)
	{
		parent::__construct($firstName, $lastName, $role, $age, $height, $contacts, $email);
	}

}