<?php

declare(strict_types=1);

namespace libMarshal;

use function ucfirst;
use libMarshal\attributes\Field;
use libMarshal\attributes\Renamer;

#[Renamer("ucfirst")] class UserRenamer extends User {
	use MarshalTrait;

	/**
	 * @param string $firstName
	 * @param string $lastName
	 * @param int $age
	 * @param float $height
	 * @param string[] $contacts
	 * @param string|null $email
	 */
	public function __construct(
		#[Field(name: "First-name")] public string $firstName,
		#[Field(name: "Last-name")] public string $lastName,
		int $age,
		float $height,
		array $contacts = [],
		?string $email = null,
	)
	{
		parent::__construct($firstName, $lastName, $age, $height, $contacts, $email);
	}
}