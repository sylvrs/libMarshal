<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<object, U>
 */
interface ObjectParseable extends Parseable {

	/**
	 * @param object $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return object
	 */
	public function serialize(mixed $value): object;
}