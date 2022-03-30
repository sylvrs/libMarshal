<?php

declare(strict_types=1);

namespace libMarshal;

use PHPUnit\Framework\TestCase;

final class UnmarshalTest extends TestCase {

	public function testUnmarshalUser(): void {
		$user = new User("John", "Doe", 42, "test@gmail.com");
		$this->assertEquals(User::unmarshal($user->marshal(), false), $user);
	}

}