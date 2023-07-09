<?php

declare(strict_types=1);

namespace benchmark;

final class Vehicle {
	/** @required */
	public string $make;
	/** @required */
	public string $model;
	/** @required */
	public int $year;
	/** @required */
	public int $mileage;
	/** @required */
	public string $color;
}