<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<string, U>
 */
interface StringParseable extends Parseable {

	/**
	 * @param string $value
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return string
	 */
	public function serialize(mixed $value): string;
}