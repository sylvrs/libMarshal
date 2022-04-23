<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<float, U>
 */
interface FloatParseable extends Parseable {

	/**
	 * @param float $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return float
	 */
	public function serialize(mixed $value): float;
}