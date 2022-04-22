<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\parser\Parseable;

/** @implements Parseable<array, Options|null> */
class OptionsParser implements Parseable {

	/**
	 * @param array|null $data
	 * @return Options|null
	 */
	public function parse(mixed $data): ?Options {
		return is_array($data) ? new Options(
			name: $data["name"],
			type: $data["type"],
			testField: $data["testField"]
		) : null;
	}

	/**
	 * @param Options|null $data
	 * @return array|null
	 */
	public function serialize(mixed $data): ?array {
		return $data instanceof Options ? [
			"name" => $data->name,
			"type" => $data->type,
			"testField" => $data->testField
		] : null;
	}
}