<?php

declare(strict_types=1);

namespace sylvrs\libMarshal;

use PHPUnit\Framework\TestCase;
use sylvrs\libMarshal\exception\UnmarshalException;
use sylvrs\libMarshal\property\IntProperty;

final class UnmarshalTest extends TestCase {

	public function testUnmarshalUser(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::ADMIN,
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "test@gmail.com"
		);
		$this->assertEquals(User::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalEmbeddedUser(): void {
		$options = new Options(name: "Test", type: "Embedded Options", testField: 456);
		$user = new EmbeddedUser(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com",
			options: $options
		);
		$this->assertEquals(EmbeddedUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalEmbeddedUserWithNullOptions(): void {
		$user = new EmbeddedUser(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com"
		);
		$this->assertEquals(EmbeddedUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalUnionUserWithInt(): void {
		$user = new UnionUser(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com",
			testField: 456
		);
		$this->assertEquals(UnionUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalUnionUserWithString(): void {
		$user = new UnionUser(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			contacts: ["janedoe@gmail.com", "jimdoe@gmail.com"],
			email: "johndoe@gmail.com",
			testField: "test"
		);
		$this->assertEquals(UnionUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalPropertyFilledUser(): void {
		$user = new PropertyFilledUser(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			property: new IntProperty(123),
			contacts: []
		);
		$this->assertEquals(PropertyFilledUser::unmarshal($user->marshal()), $user);
	}

	public function testUnmarshalWithMissingField(): void {
		$this->expectException(UnmarshalException::class);
		$this->expectExceptionMessage("Missing field 'first-name'");
		User::unmarshal(data: [
			"last-name" => "Doe",
			"age" => 42,
			"height" => 1.78,
			"contacts" => [],
			"email" => "johndoe@email.com",
		]);
	}

	public function testUnmarshalWithWrongTypeField(): void {
		$this->expectException(UnmarshalException::class);
		$this->expectExceptionMessage("Field 'firstName' must be of type 'string', got 'int'");
		User::unmarshal(data: [
			"first-name" => 123,
			"last-name" => "Doe",
			"age" => 42,
			"height" => 1.78,
			"contacts" => [],
			"email" => "johndoe@email.com",
		]);
	}

	public function testUnmarshalUserWithUninitializedFields(): void {
		$user = UserWithUninitializedField::unmarshal([
			"first-name" => "John",
			"last-name" => "Doe",
			"role" => "ADMIN",
			"age" => 42,
			"height" => 1.78,
			"contacts" => [],
		]);
		$this->assertFalse(isset($user->uninitializedField));
	}

}