<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\property\IntProperty;
use libMarshal\property\StringProperty;

class PropertyFilledUser extends User {
	use MarshalTrait;
	/**
	 * @param array<string> $contacts
	 */
	public function __construct(
		string $firstName,
		string $lastName,
		UserRole $role,
		int $age,
		float $height,
		public IntProperty|StringProperty $property,
		array $contacts = [],
		?string $email = null,
	) {
		parent::__construct($firstName, $lastName, $role, $age, $height, $contacts, $email);
	}

}