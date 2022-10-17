<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 *
 * @template K of array-key
 * @template V of mixed
 *
 * @extends Parseable<array<K, V>, U>
 */
interface ArrayParseable extends Parseable {

	/**
	 * @param array<K, V> $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return array<K,V>
	 */
	public function serialize(mixed $value): array;
}
