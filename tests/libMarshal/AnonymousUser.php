<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Exclude;

final class AnonymousUser extends User {
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
		#[Exclude] public string $anonymousField,
		array $contacts = [],
		?string $email = null,
	)
	{
		parent::__construct(
			firstName: $firstName,
			lastName: $lastName,
			role: $role,
			age: $age,
			height: $height,
			contacts: $contacts,
			email: $email,
		);
	}
}