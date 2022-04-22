<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<object, U>
 */
interface ObjectParseable extends Parseable {

	/**
	 * @param object $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return object
	 */
	public function serialize(mixed $data): object;
}