<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\parser\Parseable;

/** @implements Parseable<array, Options|null> */
class OptionsParser implements Parseable {

	/**
	 * @param array|null $value
	 * @return Options|null
	 */
	public function parse(mixed $value): ?Options {
		return is_array($value) ? new Options(
			name: $value["name"],
			type: $value["type"],
			testField: $value["testField"]
		) : null;
	}

	/**
	 * @param Options|null $value
	 * @return array|null
	 */
	public function serialize(mixed $value): ?array {
		return $value instanceof Options ? [
			"name" => $value->name,
			"type" => $value->type,
			"testField" => $value->testField
		] : null;
	}
}