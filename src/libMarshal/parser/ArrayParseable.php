<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<array, U>
 */
interface ArrayParseable extends Parseable {

	/**
	 * @param array $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return array
	 */
	public function serialize(mixed $data): array;
}