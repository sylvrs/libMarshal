<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\parser\Parseable;
use function is_array;

/** @implements Parseable<array<string, string|int>|null, Options|null> */
class OptionsParser implements Parseable {

	/**
	 * @param array<string, string|int>|null $value
	 */
	public function parse(mixed $value): ?Options {
		return is_array($value) ? new Options(
			name: (string) $value["name"],
			type: (string) $value["type"],
			testField: (int) $value["testField"]
		) : null;
	}

	/**
	 * @param Options|null $value
	 * @return array<string, mixed>|null
	 */
	public function serialize(mixed $value): ?array {
		return $value instanceof Options ? [
			"name" => $value->name,
			"type" => $value->type,
			"testField" => $value->testField
		] : null;
	}
}