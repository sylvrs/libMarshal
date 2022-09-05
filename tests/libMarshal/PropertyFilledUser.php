<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;
use libMarshal\property\IntProperty;
use libMarshal\property\StringProperty;

class PropertyFilledUser extends User {

	/**
	 * @param array<string> $contacts
	 */
	public function __construct(
		string $firstName,
		string $lastName,
		int $age,
		float $height,
		#[Field] public IntProperty|StringProperty $property,
		array $contacts = [],
		?string $email = null,
	) {
		parent::__construct($firstName, $lastName, $age, $height, $contacts, $email);
	}

}