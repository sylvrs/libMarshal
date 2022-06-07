<?php

declare(strict_types=1);

namespace libMarshal;

use PHPUnit\Framework\TestCase;
use libMarshal\attributes\Renamer;
use function ucfirst;

final class UnmarshalTest extends TestCase {

	public function testUnmarshalUser(): void {
		$user = new User(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "test@gmail.com");
		$this->assertEquals(User::unmarshal($user->marshal(), false), $user);
	}

	public function testUnmarshalUserRenamer(): void {
		$class = new class #[Renamer("ucfirst")](firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "test@gmail.com") extends User {};
		$this->assertEquals($class::unmarshal($user->marshal(), false), $user);
	}

	public function testUnmarshalEmbeddedUser(): void {
		$options = new Options(name: "Test", type: "Embedded Options", testField: 456);
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", options: $options);
		$this->assertEquals(EmbeddedUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalEmbeddedUserWithNullOptions(): void {
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com");
		$this->assertEquals(EmbeddedUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalUnionUserWithInt(): void {
		$user = new UnionUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", testField: 456);
		$this->assertEquals(UnionUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalUnionUserWithString(): void {
		$user = new UnionUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", testField: "test");
		$this->assertEquals(UnionUser::unmarshal($user->marshal()), $user);
	}

}