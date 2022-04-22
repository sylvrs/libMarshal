<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<bool, U>
 */
interface BooleanParseable extends Parseable {

	/**
	 * @param bool $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return bool
	 */
	public function serialize(mixed $data): bool;
}