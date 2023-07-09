<?php

declare(strict_types=1);

namespace benchmark;

use CuyZ\Valinor\MapperBuilder;
use JsonMapper;
use JsonMapper\JsonMapperFactory;

// requires for the benchmark
require_once "Vehicle.php";
require_once "SelfVehicle.php";

final class UnmarshalBench {
	public const BENCHMARK_DATA = [
		"make" => "Ford",
		"model" => "Mustang",
		"year" => 1969,
		"mileage" => 100000,
		"color" => "red",
	];

	public function benchJsonMapper(): void {
		static $jsonMapper = null;
		if ($jsonMapper === null) {
			$jsonMapper = (new JsonMapperFactory())->default();
		}
		$jsonMapper->mapObject((object) self::BENCHMARK_DATA, new Vehicle());
	}

	public function benchCweiskeJsonMapper(): void {
		static $cweiskeMapper = null;
		if ($cweiskeMapper === null) {
			$cweiskeMapper = new JsonMapper();
			$cweiskeMapper->bEnforceMapType = false;
		}
		$cweiskeMapper->map(self::BENCHMARK_DATA, new Vehicle());
	}

	public function benchValinor(): void {
		static $valinorMapper = null;
		if ($valinorMapper === null) {
			$valinorMapper = (new MapperBuilder())->mapper();
		}
		$valinorMapper->map(Vehicle::class, self::BENCHMARK_DATA);
	}

	public function benchLibMarshal(): void {
		SelfVehicle::unmarshal(self::BENCHMARK_DATA);
	}
}