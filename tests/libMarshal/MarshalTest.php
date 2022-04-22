<?php

declare(strict_types=1);

namespace libMarshal;

use PHPUnit\Framework\TestCase;

final class MarshalTest extends TestCase {

	public function testMarshalOptions(): void {
		$options = new Options(
			name: "Test", type: "Options",
			testField: 123, unmarshaledField: true
		);
		$this->assertEquals([
			"name" => "Test",
			"type" => "Options",
			"testField" => 123
		], $options->marshal());
	}

	public function testMarshalUser(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			age: 42,
			email: "johndoe@gmail.com"
		);

		$this->assertEquals([
			"first-name" => "John",
			"last-name" => "Doe",
			"age" => 42,
			"email" => "johndoe@gmail.com"
		], $user->marshal());
	}

	public function testMarshalEmbeddedUser(): void {
		$options = new Options(name: "Test", type: "Embedded Options", testField: 456, unmarshaledField: false);
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, email: "johndoe@gmail.com", options: $options);

		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"email" => "johndoe@gmail.com",
				"embedded-options" => ["name" => "Test", "type" => "Embedded Options", "testField" => 456]
			],
			$user->marshal()
		);
	}

	public function testMarshalEmbeddedUserWithNullOptions(): void {
		$user = new EmbeddedUser(firstName: "John", lastName: "Doe", age: 42, email: "johndoe@gmail.com");
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"age" => 42,
				"email" => "johndoe@gmail.com",
				"embedded-options" => null
			],
			$user->marshal()
		);
	}

}