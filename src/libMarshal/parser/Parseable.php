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
 * @template TSerialized
 * @template TParsed
 */
interface Parseable {
	/**
	 * @param TSerialized $value
	 * @return TParsed
	 */
	public function parse(mixed $value): mixed;

	/**
	 * @param TParsed $value
	 * @return TSerialized
	 */
	public function serialize(mixed $value): mixed;
}