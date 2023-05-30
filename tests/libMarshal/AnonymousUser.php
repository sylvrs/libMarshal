<?php

declare(strict_types=1);

namespace libMarshal;

final class AnonymousUser extends User {
	use MarshalTrait;

	/**
	 * @param array<string> $contacts
	 */
	public function __construct(
		string                 $firstName,
		string                 $lastName,
		int                    $age,
		float                  $height,
		public readonly string $anonymousField,
		array                  $contacts = [],
		?string                $email = null,
	)
	{
		parent::__construct(
			firstName: $firstName,
			lastName: $lastName,
			age: $age,
			height: $height,
			contacts: $contacts,
			email: $email,
		);
	}
}