<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * @template U of mixed
 * @extends Parseable<string, U>
 */
interface StringParseable extends Parseable {

	/**
	 * @param string $data
	 * @return U
	 */
	public function parse(mixed $data): mixed;

	/**
	 * @param U $data
	 * @return string
	 */
	public function serialize(mixed $data): string;
}