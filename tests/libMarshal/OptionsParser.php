<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\parser\Parseable;

/** @implements Parseable<array, Options> */
class OptionsParser implements Parseable {

	/**
	 * @param array $data
	 * @return Options
	 */
	public function parse(mixed $data): Options {
		return new Options(
			name: $data["name"],
			type: $data["type"],
			testField: $data["testField"]
		);
	}

	/**
	 * @param Options $data
	 * @return array
	 */
	public function serialize(mixed $data): array {
		return [
			"name" => $data->name,
			"type" => $data->type,
			"testField" => $data->testField
		];
	}
}