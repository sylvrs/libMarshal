<?php

declare(strict_types=1);

namespace sylvrs\libMarshal\property;

use sylvrs\libMarshal\MarshalTrait;

final class StringProperty {
	use MarshalTrait;

	public function __construct(protected string $value) {
	}
}