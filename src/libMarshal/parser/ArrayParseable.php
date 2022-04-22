<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 *
 * @template K of mixed
 * @template V of mixed
 *
 * @extends Parseable<array, U>
 */
interface ArrayParseable extends Parseable {

	/**
	 * @param array<K,V> $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return array<K,V>
	 */
	public function serialize(mixed $data): array;
}