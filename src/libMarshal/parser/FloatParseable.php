<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<float, U>
 */
interface FloatParseable extends Parseable {

	/**
	 * @param float $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return float
	 */
	public function serialize(mixed $data): float;
}