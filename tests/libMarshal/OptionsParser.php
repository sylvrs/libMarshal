<?php

declare(strict_types=1);

namespace libMarshal;

/** @implements Parseable<Options> */
class OptionsParser implements Parseable {

	public function parse(mixed $data): Options {
		assert(is_array($data), "Expected array, got " . gettype($data));
		return new Options(
			name: $data["name"],
			type: $data["type"],
			testField: $data["testField"]
		);
	}

	/**
	 * @param mixed $data
	 * @return array
	 */
	public function serialize(mixed $data): mixed {
		assert($data instanceof Options, "Expected Options, got " . get_class($data));
		return [
			"name" => $data->name,
			"type" => $data->type,
			"testField" => $data->testField
		];
	}
}