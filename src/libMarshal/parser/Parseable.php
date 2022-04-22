<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template T of mixed
 * @template U of mixed
 */
interface Parseable {

	/**
	 * Given an array of data, this will return a value
	 *
	 * @param T $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return T
	 */
	public function serialize(mixed $data): mixed;

}