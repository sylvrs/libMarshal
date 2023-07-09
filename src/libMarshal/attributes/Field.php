<?php

declare(strict_types=1);

namespace libMarshal\attributes;

use Attribute;
use libMarshal\parser\Parseable;
use function is_string;

/**
 * @template-covariant TSerialized of mixed
 * @template-covariant TParsed of mixed
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Field {
	/** @var Parseable<TSerialized, TParsed>|null  */
	public readonly ?Parseable $parser;

	/**
	 * @param string $name - This is the name of the field when marshaling/unmarshaling.
	 * @param Parseable<TSerialized, TParsed>|class-string<Parseable<TSerialized, TParsed>>|null $parser - This is the class that will be used to parse & serialize the value.
	 * @param bool $allowUninitialized - If set to true, the field will not be required to be initialized when unmarshaling.
	 * @param bool $resolveEnumByName - If set to true, backed enums will be unmarshaled by name instead of by value.
	 */
	public function __construct(
		public readonly string $name = "",
		Parseable|string|null  $parser = null,
		public readonly bool   $allowUninitialized = false,
		public readonly bool   $resolveEnumByName = false,
	) {
		$this->parser = match (true) {
			$parser instanceof Parseable => $parser,
			is_string($parser) => new $parser(),
			default => null,
		};
	}
}