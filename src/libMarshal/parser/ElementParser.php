<?php

declare(strict_types=1);

namespace libMarshal\parser;

use function array_keys;
use function array_map;
use function array_values;

/**
 * @template TKey of array-key
 * @template TSerialized of mixed
 * @template TParsed of mixed
 */
abstract class ElementParser {

	/**
	 * @param array<TKey,TSerialized> $value
	 * @return array<array-key, TParsed>
	 */
	public function parse(mixed $value): array {
		return array_map(
			fn(mixed $key, mixed $currentValue): mixed => $this->parseElement($key, $currentValue),
			array_keys($value),
			array_values($value)
		);
	}

	/**
	 * Parses the element from the serialized value into a value of type TParsed
	 *
	 * @param TKey $key
	 * @param TSerialized $value
	 * @return TParsed
	 */
	public abstract function parseElement(mixed $key, mixed $value): mixed;

	/**
	 * @param array<array-key, TParsed> $value
	 * @return array<TKey,TSerialized>
	 */
	public function serialize(mixed $value): array {
		return array_map(
			fn(mixed $key, mixed $currentValue): mixed => $this->serializeElement($key, $currentValue),
			array_keys($value),
			array_values($value)
		);
	}

	/**
	 * Serializes the element from the parsed value into a value of type TSerialized
	 *
	 * @param TKey $key
	 * @param TParsed $value
	 * @return TSerialized
	 */
	public abstract function serializeElement(mixed $key, mixed $value): mixed;
}