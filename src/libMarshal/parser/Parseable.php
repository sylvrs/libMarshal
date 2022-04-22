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