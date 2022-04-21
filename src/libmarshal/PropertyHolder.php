<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;

/**
 * The PropertyHolder class is a wrapper class that holds a couple of key pieces of information about a property:
 * 1. The {@link ReflectionProperty} instance - Used to get the property's name and type as well as an instance's value.
 * 2. The annotated {@link Field} instance - Used to get an alternate name for the property when marshalling/unmarshalling
 */
class PropertyHolder {
	/**
	 * This is used to store a list of ReflectionClass instances associated with the property's type
	 *
	 * @var array<ReflectionClass<object>>
	 */
	protected array $typeClasses;

	public function __construct(
		protected ReflectionProperty $property,
		protected Field $field
	)
	{
	}

	/**
	 * This method is a lazy-loaded getter to get all
	 * reflection classes associated with the property's type
	 *
	 * @return array<ReflectionClass<object>>
	 * @throws ReflectionException
	 */
	public function getTypeClasses(): array {
		return $this->typeClasses ??= $this->createTypeClasses();
	}

	/**
	 * This method is the actual logic behind the {@link getTypeClasses()} method.
	 *
	 * @return array<ReflectionClass<object>>
	 * @throws ReflectionException
	 */
	private function createTypeClasses(): array {
		$typeClasses = self::resolveClassesFromType($this->property->getType());
		foreach($typeClasses as $typeClass) {
			if(!MarshalTrait::hasTrait($typeClass, MarshalTrait::class)) {
				throw new RuntimeException("The type '{$typeClass->getName()}' is not a marshal type");
			}
		}
		return $typeClasses;
	}

	/**
	 * This method is used to determine whether
	 * a property's type(s) allow for a null value
	 *
	 * @return bool
	 */
	public function allowsNull(): bool {
		return $this->property->getType()?->allowsNull() ?? false;
	}

	/**
	 * Returns an array of properties that is intended to be destructured.
	 *
	 * @return array<int, mixed>
	 */
	public function asArray(): array {
		return [$this->property, $this->field];
	}

	/**
	 * A static method used to resolved a type to an array of ReflectionClass instances
	 *
	 * @param ReflectionType|null $type
	 * @return array<ReflectionClass<object>>
	 * @throws ReflectionException
	 */
	private static function resolveClassesFromType(?ReflectionType $type): array {
		return match(true) {
			$type instanceof ReflectionNamedType => [new ReflectionClass($type->getName())],
			$type instanceof ReflectionUnionType => array_map(
				callback: static fn (ReflectionNamedType $type) => new ReflectionClass($type->getName()),
				array: $type->getTypes()
			),
			default => []
		};
	}

}