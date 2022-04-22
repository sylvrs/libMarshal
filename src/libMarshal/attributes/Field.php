<?php

declare(strict_types=1);

namespace libMarshal\attributes;

use Attribute;
use libMarshal\Parseable;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field {

	/** @var Parseable<mixed>|null */
	protected ?Parseable $parser = null;

	/**
	 * @param string $name
	 * @param class-string<Parseable<mixed>>|null $parser - This is the class that will be used to parse & serialize the value.
	 */
	public function __construct(
		public string $name = "",
		?string $parser = null
	)
	{
		$this->parser = $parser ? new $parser : null;
	}

	/**
	 * @return Parseable<mixed>|null
	 */
	public function getParser(): ?Parseable
	{
		return $this->parser;
	}
}