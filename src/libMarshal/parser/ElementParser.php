<?php

declare(strict_types=1);

namespace libMarshal\parser;

use function array_keys;
use function array_map;
use function array_values;

/**
 * @template U of mixed
 *
 * @template K of array-key
 * @template V of mixed
 *
 * @implements ArrayParseable<array<U>, K, V>
 */
abstract class ElementParser implements ArrayParseable {

	/**
	 * @param array<K,V> $value
	 * @return array<array-key, U>
	 */
	public function parse(mixed $value): array {
		return array_map(
			fn(mixed $key, mixed $currentValue): mixed => $this->parseElement($key, $currentValue),
			array_keys($value),
			array_values($value)
		);
	}

	public abstract function parseElement(mixed $key, mixed $value): mixed;

	/**
	 * @param array<array-key, U> $value
	 * @return array<K,V>
	 */
	public function serialize(mixed $value): array {
		return array_map(
			fn(mixed $key, mixed $currentValue): mixed => $this->serializeElement($key, $currentValue),
			array_keys($value),
			array_values($value)
		);
	}

	public abstract function serializeElement(mixed $key, mixed $value): mixed;
}