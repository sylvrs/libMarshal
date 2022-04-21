<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class EmbeddedUser extends User {
	use MarshalTrait;

	public function __construct(
		string $firstName,
		string $lastName,
		int $age,
		string $email,
		#[Field(name: "embedded-options")]
		public ?Options $options = null
	)
	{
		parent::__construct($firstName, $lastName, $age, $email);
	}
}