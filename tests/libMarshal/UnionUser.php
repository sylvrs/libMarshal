<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class UnionUser extends User {
	use MarshalTrait;

	public function __construct(
		string $firstName,
		string $lastName,
		int $age,
		float $height,
		array $contacts = [],
		?string $email = null,
		#[Field(name: "test-field")]
		protected int|string $testField = 0
	) {
		parent::__construct($firstName, $lastName, $age, $height, $contacts, $email);
	}

}