<?php

declare(strict_types=1);

namespace sylvrs\libMarshal;

use sylvrs\libMarshal\attributes\Field;
use sylvrs\libMarshal\parser\Parseable;
use ReflectionClass;
use ReflectionEnum;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;
use function array_filter;
use function array_map;
use function count;

/**
 * The PropertyHolder class is a wrapper class that holds a couple of key pieces of information about a property:
 * 1. The {@link ReflectionProperty} instance - Used to get the property's name and type as well as an instance's value.
 * 2. The annotated {@link Field} instance - Used to get an alternate name for the property when marshalling/unmarshalling
 *
 * @template TSerialized of mixed
 * @template TParsed of mixed
 */
class PropertyHolder {
	/**
	 * This is used to store a list of ReflectionClass instances associated with the property's type
	 *
	 * @var array<ReflectionClass<object>>
	 */
	protected array $typeClasses;

	/**
	 * If the property's type is an array, this is used to store a list of the types of the array's elements
	 * This can be specified using a doc-comment above the property
	 * @var array<mixed|ReflectionClass<object>>
	 */
	protected array $arrayTypes;

	/**
	 * @param Field<TSerialized, TParsed> $field
	 */
	public function __construct(
		protected ReflectionProperty $property,
		protected Field $field,
	) {
	}

	/**
	 * This method is a getter for the field's parser and is
	 * primarily used as a way to reduce excessive chaining of methods
	 * @return Parseable<TSerialized, TParsed>|null
	 */
	public function getParser(): ?Parseable
	{
		return $this->field->parser;
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
	 * @throws RuntimeException
	 */
	private function createTypeClasses(): array {
		$typeClasses = self::resolveClassesFromType($this->property->getType());
		if ($this->field->parser === null) {
			foreach ($typeClasses as $typeClass) {
				if (!self::hasTraitRecursive($typeClass, MarshalTrait::class) && !$typeClass->isEnum()) {
					throw new RuntimeException("The type '{$typeClass->getName()}' is not a marshal type");
				}
			}
		}
		return $typeClasses;
	}

	/**
	 * This method is used to determine whether
	 * a property's type(s) allow for a null value
	 */
	public function allowsNull(): bool {
		return $this->property->getType()?->allowsNull() ?? true;
	}

	/**
	 * Returns an array of properties that is intended to be destructured.
	 *
	 * @return array{0: ReflectionProperty, 1: Field<TSerialized, TParsed>}
	 */
	public function asArray(): array {
		return [$this->property, $this->field];
	}

	public function getProperty(): ReflectionProperty {
		return $this->property;
	}

	/**
	 * @return Field<TSerialized, TParsed>
	 */
	public function getField(): Field {
		return $this->field;
	}

	/**
	 * A static method used to resolve a type to an array of ReflectionClass instances
	 *
	 * @return array<ReflectionClass<object>>
	 */
	private static function resolveClassesFromType(?ReflectionType $type): array {
		return array_filter(
			array: match (true) {
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
	 * @return ReflectionClass<object>|null
	 */
	private static function resolveNamedTypeToClass(ReflectionNamedType $type): ?ReflectionClass {
		if ($type->isBuiltin()) {
			return null;
		}
		/** @var class-string<object> $typeName */
		$typeName = $type->getName();
		$reflected = new ReflectionClass($typeName);
		return $reflected->isEnum() ? new ReflectionEnum($typeName) : $reflected;
	}

	/**
	 * Returns true if a given class has a trait on it
	 *
	 * @param ReflectionClass<object> $class
	 * @param class-string<object> $traitClass
	 */
	private static function hasTrait(ReflectionClass $class, string $traitClass): bool {
		return count(array_filter($class->getTraits(), fn(ReflectionClass $trait) => $trait->getName() === $traitClass)) === 1;
	}

	/**
	 * A recursive check that checks if a class or any of its parents has a given trait
	 *
	 * @param ReflectionClass<object> $class - The original class to check
	 * @param class-string<object> $traitClass - The class name of the trait to check for
	 * @param int $maxIterations - The maximum number of inheritance levels to check (e.g., child -> parent -> grandparent, etc.)
	 * @return bool - Returns true if the class or any of its parents has the trait
	 * @throws RuntimeException - Thrown if the maximum number of iterations is exceeded
	 */
	private static function hasTraitRecursive(ReflectionClass $class, string $traitClass, int $maxIterations = 10): bool {
		if ($maxIterations <= 0) {
			throw new RuntimeException("Maximum number of iterations exceeded");
		}

		return match (true) {
			self::hasTrait($class, $traitClass) => true,
			$class->getParentClass() instanceof ReflectionClass => self::hasTraitRecursive($class->getParentClass(), $traitClass, $maxIterations - 1),
			default => false
		};
	}

}