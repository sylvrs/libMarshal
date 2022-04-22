<?php

declare(strict_types=1);

namespace libMarshal;

/**
 * @template T of mixed
 */
interface Parseable {

	/**
	 * Given an array of data, this will return a value
	 *
	 * @param mixed $data
	 * @return T
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param T $data
	 * @return mixed
	 */
	public function serialize(mixed $data): mixed;

}