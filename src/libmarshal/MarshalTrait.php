<?php
declare(strict_types=1);

namespace libMarshal;


use libMarshal\attributes\Field;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * This is the main trait used for marshaling/demarshaling data.
 *
 *
 * A good use case for this would be with data classes. For example:
 *
 * class MyDataClass {
 * 	  use MarshalTrait;
 *
 *     #[Field]
 *     public int $testValue;
 *     #[Field]
 *     public bool $testBool;
 *     #[Field]
 *     public string $testString;
 * }
 */
trait MarshalTrait {
	/**
	 * A ReflectionClass instance used to fetch properties, values, etc.
	 * @var ReflectionClass<static>
	 */
	private static ReflectionClass $reflectedInstance;
	/**
	 * A list of {@link PropertyHolder} instances used to fetch properties, field attributes, etc.
	 * @var array<int, PropertyHolder>
	 */
	private static array $cachedHolders;

	/**
	 * @throws GeneralMarshalException
	 */
	public function marshal(): array {
		try {
			$data = [];
			foreach (self::getHolders() as $holder) {
				// Get the reflection property + field attribute from the holder
				[$property, $field] = $holder->asArray();

				// Get the current value of the property
				$value = $this->{$property->getName()};
				// If the value is an object and has any type classes with the trait, we override
				if(is_object($value) && count($holder->getTypeClasses()) > 0) {
					$value = $value->marshal();
				}
				// Update the array with the value
				$name = $field->name !== "" ? $field->name : $property->getName();
				$data[$name] = $value;
			}
			return $data;
		} catch(ReflectionException $exception) {
			throw new GeneralMarshalException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * @param array $data - The data to unmarshal with
	 * @param bool $strict - Whether to throw an exception if a field is missing
	 * @return MarshalTrait
	 * @throws GeneralMarshalException
	 * @throws UnmarshalException
	 * @noinspection PhpRedundantCatchClauseInspection - Given that we are using reflection to invoke the unmarshal method, we can ignore PHPStorm's warning about redundancy
	 */
	public static function unmarshal(array $data, bool $strict = true): static {
		try {
			/** @var static $instance */
			$instance = self::getReflectedInstance()->newInstanceWithoutConstructor();
			foreach(self::getHolders() as $holder) {
				// Get the property and field from the holder
				[$property, $field] = $holder->asArray();

				// Get the name of the property
				$name = $field->name !== "" ? $field->name : $property->getName();
				// Fetch the value
				$value = $data[$name] ?? null;
				// If the value is null & strict is true, throw an exception
				if($value === null && !$holder->allowsNull() && $strict) {
					throw new UnmarshalException("Missing field '$name'");
				}
				// If the value is an array, we can check if it has the MarshalTrait and if so, we can unmarshal it
				if(is_array($value) && count($holder->getTypeClasses()) > 0) {
					foreach($holder->getTypeClasses() as $type) {
						try {
							$method = $type->getMethod("unmarshal");
							$value = $method->invoke($instance, $value, $strict);
							break;
						}
						catch(ReflectionException|UnmarshalException $_) {
							// We don't care about this exception, we just want to try the next type
						}
					}
				} else {
					// Ensure that the value is of the correct type
					self::checkType($property, $value);
				}
				$instance->{$property->getName()} = $value;
			}
			return $instance;
		} catch(ReflectionException $exception) {
			throw new GeneralMarshalException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	private static function getReflectedInstance(): ReflectionClass {
		return self::$reflectedInstance ??= new ReflectionClass(static::class);
	}

	/**
	 * A lazy getter/initializer for getting the {@link PropertyHolder} instances for the implementing class.
	 *
	 * @return array<int, PropertyHolder>
	 */
	private static function getHolders(): array {
		return self::$cachedHolders ??= array_map(
			callback: static fn(ReflectionProperty $property) => new PropertyHolder(
				property: $property,
				field: self::getField($property)
			),
			array: array_filter(
				array: self::getReflectedInstance()->getProperties(),
				callback: fn(ReflectionProperty $property): bool => self::getField($property) !== null
			)
		);
	}

	/**
	 * Attempts to get the Field attribute from a property
	 * If the property doesn't have the Field attribute, it will return null
	 *
	 * @param ReflectionProperty $property
	 * @return Field|null
	 */
	private static function getField(ReflectionProperty $property): ?Field {
		$attribute = $property->getAttributes(Field::class)[0] ?? null;
		if($attribute === null) {
			return null;
		}
		$field = $attribute->newInstance();
		assert($field instanceof Field, "Field attribute must be an instance of Field");
		return $field;
	}

	/**
	 * Returns true if a given class has a trait on it
	 *
	 * @param ReflectionClass $class
	 * @param class-string $traitClass
	 * @return bool
	 */
	public static function hasTrait(ReflectionClass $class, string $traitClass): bool {
		return count(array_filter($class->getTraits(), fn(ReflectionClass $trait) => $trait->getName() === $traitClass)) === 1;
	}

	/**
	 * @throws GeneralMarshalException
	 */
	private static function checkType(ReflectionProperty $property, mixed $value): void {
		$type = $property->getType();
		if($type === null || ($value === null && $type->allowsNull())) {
			return;
		}
		$valueTypeName = get_debug_type($value);
		if($type instanceof ReflectionNamedType && $valueTypeName !== $type->getName()) {
			throw new GeneralMarshalException("Field '{$property->getName()}' must be of type '{$type->getName()}', got '$valueTypeName'");
		} else if($type instanceof ReflectionUnionType && !in_array($valueTypeName, $type->getTypes(), true)) {
			$types = implode(
				separator: ",",
				array: array_map(
					callback: fn(ReflectionNamedType $type) => $type->getName(),
					array: $type->getTypes()
				)
			);
			throw new GeneralMarshalException("Field '{$property->getName()}' must be one of the types ($types), got '$valueTypeName'");
		}
		// TODO: 8.1 now supports ReflectionIntersectionType, so when that is more widely used, we can add support for it here
	}

}