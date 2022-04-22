<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<int, U>
 */
interface IntParseable extends Parseable {

	/**
	 * @param int $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return int
	 */
	public function serialize(mixed $value): int;
}