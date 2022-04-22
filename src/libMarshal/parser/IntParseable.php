<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<int, U>
 */
interface IntParseable extends Parseable {

	/**
	 * @param int $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return int
	 */
	public function serialize(mixed $data): int;
}