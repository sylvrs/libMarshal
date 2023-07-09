<?php

declare(strict_types=1);

namespace libMarshal\parser;

use function array_map;

/**
 * @template TSerialized of mixed
 * @template TParsed of mixed
 *
 * @implements Parseable<array<TSerialized>, array<TParsed>>
 */
abstract class ElementParser implements Parseable {

	/**
	 * @param array<TSerialized> $value
	 * @return array<TParsed>
	 */
	public function parse(mixed $value): array {
		return array_map(
			fn (mixed $element): mixed => $this->parseElement($element),
			$value
		);
	}

	/**
	 * Parses the element from the serialized value into a value of type TParsed
	 *
	 * @param TSerialized $value
	 * @return TParsed
	 */
	public abstract function parseElement(mixed $value): mixed;

	/**
	 * @param array<TParsed> $value
	 * @return array<TSerialized>
	 */
	public function serialize(mixed $value): array {
		return array_map(
			fn (mixed $element): mixed => $this->serializeElement($element),
			$value
		);
	}

	/**
	 * Serializes the element from the parsed value into a value of type TSerialized
	 *
	 * @param TParsed $value
	 * @return TSerialized
	 */
	public abstract function serializeElement(mixed $value): mixed;
}