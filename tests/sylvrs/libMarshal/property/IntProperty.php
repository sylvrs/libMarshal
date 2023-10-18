<?php

declare(strict_types=1);

namespace sylvrs\libMarshal\property;

use sylvrs\libMarshal\MarshalTrait;

final class IntProperty {
	use MarshalTrait;

	public function __construct(protected int $value) {
	}

}