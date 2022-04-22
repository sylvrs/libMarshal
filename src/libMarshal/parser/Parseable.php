<?php

declare(strict_types=1);

namespace libMarshal\parser;

/**
 * The `Parseable` interface is used when defining custom parsing of a field.
 * To specify custom parsing, create a class that implements this interface and
 * pass the class string to the `Field` constructor using the `parser` property.
 *
 * As an example:
 * #[Field(name: "Test", parser: TestParser::class)]
 * protected TestClass $test;
 *
 * @template T of mixed - The type to parse from.
 * @template U of mixed - The type to parse to.
 */
interface Parseable {

	/**
	 * @param T $value - The data to parse
	 * @return U
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param U $value
	 * @return T
	 */
	public function serialize(mixed $value): mixed;

}