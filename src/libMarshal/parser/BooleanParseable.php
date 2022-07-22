<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<bool, U>
 */
interface BooleanParseable extends Parseable {

	/**
	 * @param bool $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 */
	public function serialize(mixed $value): bool;
}