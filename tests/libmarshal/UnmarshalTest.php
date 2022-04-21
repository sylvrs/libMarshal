<?php

declare(strict_types=1);

namespace libMarshal;

use PHPUnit\Framework\TestCase;

final class UnmarshalTest extends TestCase {

	public function testUnmarshalUser(): void {
		$user = new User("John", "Doe", 42, "test@gmail.com");
		$this->assertEquals(User::unmarshal($user->marshal(), false), $user);
	}

	public function testUnmarshalEmbeddedUserWithNullOptions(): void {
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, email: "johndoe@gmail.com");
		$this->assertEquals(User::unmarshal($user->marshal(), false), $user);
	}

}