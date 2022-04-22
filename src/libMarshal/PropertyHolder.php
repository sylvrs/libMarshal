<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Field;
use libMarshal\parser\Parseable;
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
	 * This method is a getter for the field's parser and is
	 * primarily used as a way to reduce excessive chaining of methods
	 *
	 * @return Parseable<mixed, mixed>|null
	 */
	public function getParser(): ?Parseable
	{
		return $this->field->getParser();
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
		if($this->field->getParser() === null) {
			foreach($typeClasses as $typeClass) {
				if(!self::hasTrait($typeClass, MarshalTrait::class)) {
					throw new RuntimeException("The type '{$typeClass->getName()}' is not a marshal type");
				}
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
		return $this->property->getType()?->allowsNull() ?? true;
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
	 * A static method used to resolve a type to an array of ReflectionClass instances
	 *
	 * @param ReflectionType|null $type
	 * @return array<ReflectionClass<object>>
	 * @throws ReflectionException
	 */
	private static function resolveClassesFromType(?ReflectionType $type): array {
		return array_filter(
			array: match(true) {
				$type instanceof ReflectionNamedType => [self::resolveNamedTypeToClass($type)],
				$type instanceof ReflectionUnionType => array_map(
					callback: static fn (ReflectionNamedType $type) => self::resolveNamedTypeToClass($type),
					array: $type->getTypes()
				),
				default => []
			}
		);
	}

	/**
	 * A static method used to resolve a specific type into one of two things:
	 * 1. `ReflectionClass` - An instance if it is a class
	 * 2. `null` - Null if it is a primitive type
	 *
	 * @param ReflectionNamedType $type
	 * @return ReflectionClass<object>|null
	 * @throws ReflectionException
	 */
	private static function resolveNamedTypeToClass(ReflectionNamedType $type): ?ReflectionClass {
		return !$type->isBuiltin() ? new ReflectionClass($type->getName()) : null;
	}

	/**
	 * Returns true if a given class has a trait on it
	 *
	 * @param ReflectionClass<object> $class
	 * @param class-string<object> $traitClass
	 * @return bool
	 */
	private static function hasTrait(ReflectionClass $class, string $traitClass): bool {
		return count(array_filter($class->getTraits(), fn(ReflectionClass $trait) => $trait->getName() === $traitClass)) === 1;
	}

}