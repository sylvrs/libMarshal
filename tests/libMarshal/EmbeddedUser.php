<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;

class EmbeddedUser extends User {
	use MarshalTrait;

	/**
	 * @param string $firstName
	 * @param string $lastName
	 * @param int $age
	 * @param string[] $contacts
	 * @param string $email
	 * @param Options|null $options
	 */
	public function __construct(
		string $firstName,
		string $lastName,
		int $age,
		array $contacts,
		string $email,
		#[Field(name: "embedded-options", parser: OptionsParser::class)]
		public ?Options $options = null
	)
	{
		parent::__construct($firstName, $lastName, $age, $contacts, $email);
	}
}