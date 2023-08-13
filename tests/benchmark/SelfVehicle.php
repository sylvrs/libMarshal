<?php

declare(strict_types=1);

namespace benchmark;

use sylvrs\libMarshal\MarshalTrait;

/**
 * A separate class is needed as the other libraries try to map internal properties from MarshalTrait
 */
final class SelfVehicle {
	use MarshalTrait;

	public string $make;
	public string $model;
	public int $year;
	public int $mileage;
	public string $color;
}