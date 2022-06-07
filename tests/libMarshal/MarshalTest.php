<?php

declare(strict_types=1);

namespace libMarshal;

use PHPUnit\Framework\TestCase;
use libMarshal\attributes\Renamer;
use function ucfirst;

final class MarshalTest extends TestCase {

	public function testMarshalUser(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com"
		);

		$this->assertEquals([
			"first-name" => "John",
			"last-name" => "Doe",
			"age" => 42,
			"height" => 1.78,
			"contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
			"email" => "johndoe@gmail.com"
		], $user->marshal());
	}

	public function testMarshalRenamer(): void {
		$user = new #[Renamer("ucfirst")] class extends User(
			firstName: "John",
			lastName: "Doe",
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com"
		) {};

		$this->assertEquals([
			"First-name" => "John",
			"Last-name" => "Doe",
			"Age" => 42,
			"Height" => 1.78,
			"Contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
			"Email" => "johndoe@gmail.com"
		], $user->marshal());
	}

	public function testMarshalEmbeddedUser(): void {
		$options = new Options(name: "Test", type: "Embedded Options", testField: 456);
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", options: $options);

		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"height" => 1.78,
				"contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
				"email" => "johndoe@gmail.com",
				"embedded-options" => ["name" => "Test", "type" => "Embedded Options", "testField" => 456]
			],
			$user->marshal()
		);
	}

	public function testMarshalEmbeddedUserWithNullOptions(): void {
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com");
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"height" => 1.78,
				"contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
				"email" => "johndoe@gmail.com",
				"embedded-options" => null
			],
			$user->marshal()
		);
	}

	public function testMarshalUnionUserWithInt(): void {
		$user = new UnionUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", testField: 456);
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"height" => 1.78,
				"contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
				"email" => "johndoe@gmail.com",
				"test-field" => 456
			],
			$user->marshal()
		);
	}

	public function testMarshalUnionUserWithString(): void {
		$user = new UnionUser(firstName: "John", lastName: "Doe", age: 42, height: 1.78, contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"], email: "johndoe@gmail.com", testField: "test");
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"height" => 1.78,
				"contacts" => ["janedoe@gmail.com", "jimdoe@gmail.com"],
				"email" => "johndoe@gmail.com",
				"test-field" => "test"
			],
			$user->marshal()
		);
	}

}