<?php

declare(strict_types=1);

namespace sylvrs\libMarshal;

use PHPUnit\Framework\TestCase;
use function file_exists;
use function mkdir;

class FileTest extends TestCase {

	public const OUTPUT_DIRECTORY = "output" . DIRECTORY_SEPARATOR;

	public const JSON_PATH = self::OUTPUT_DIRECTORY . "json_user.json";
	public const YAML_PATH = self::OUTPUT_DIRECTORY . "yaml_user.yml";

	public function setUp(): void {
		if (!file_exists(self::OUTPUT_DIRECTORY)) {
			mkdir(self::OUTPUT_DIRECTORY);
		}
	}

	public function testSaveUserToJson(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42, height: 1.78,
			contacts: [],
			email: "johndoe@gmail.com"
		);
		$user->saveToJson(self::JSON_PATH);
		$this->expectNotToPerformAssertions();
	}

	/**
	 * @depends testSaveUserToJson
	 */
	public function testLoadUserFromJson(): void {
		$user = User::loadFromJson(self::JSON_PATH);
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"role" => "USER",
				"age" => 42,
				"height" => 1.78,
				"contacts" => [],
				"email" => "johndoe@gmail.com",
			],
			$user->marshal()
		);
	}

	public function testSaveUserToYaml(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 1.78,
			contacts: [],
			email: "johndoe@gmail.com"
		);
		$user->saveToYaml(self::YAML_PATH);
		$this->expectNotToPerformAssertions();
	}

	/**
	 * @depends testSaveUserToYaml
	 */
	public function testLoadUserFromYaml(): void {
		$user = User::loadFromYaml(self::YAML_PATH);
		$this->assertEquals(
			[
				"first-name" => "John",
				"last-name" => "Doe",
				"role" => "USER",
				"age" => 42,
				"height" => 1.78,
				"contacts" => [],
				"email" => "johndoe@gmail.com",
			],
			$user->marshal()
		);
	}

	public function testIntEdgeCase(): void {
		$user = new User(
			firstName: "John",
			lastName: "Doe",
			role: UserRole::USER,
			age: 42,
			height: 2,
			contacts: [],
			email: "johndoe@gmail.com"
		);
		$user->saveToJson(self::JSON_PATH);
		$user = User::loadFromJson(self::JSON_PATH);
		$this->assertEquals(2, $user->height);
	}

}