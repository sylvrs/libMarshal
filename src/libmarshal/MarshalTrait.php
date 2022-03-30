<?php
declare(strict_types=1);

namespace libMarshal;


use libMarshal\attributes\Field;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;

/**
 * This is the main trait used for marshaling/demarshaling data.
 *
 * At the moment, it can only be used for marshaling/demarshaling scalar types.
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

	public function marshal(): array {
		$data = [];
		$reflected = new ReflectionClass($this);
		foreach ($reflected->getProperties() as $property) {
			$field = self::getField($property);
			if($field === null) {
				continue;
			}
			$value = $this->{$property->getName()};
			if(is_object($value) && self::hasTrait(new ReflectionClass($value), MarshalTrait::class)) {
				$value = $value->marshal();
			}

			$name = strlen($field->name) > 0 ? $field->name : $property->getName();
			$data[$name] = $value;
		}
		return $data;
	}

	public static function unmarshal(array $data, bool $strict = true): static {
		$reflected = new ReflectionClass(static::class);
		/** @var static $instance */
		$instance = $reflected->newInstanceWithoutConstructor();
		foreach($reflected->getProperties() as $property) {
			$field = self::getField($property);
			if($field === null) {
				continue;
			}
			$name = strlen($field->name) > 0 ? $field->name : $property->getName();
			$value = $data[$name] ?? null;
			if($value === null && $strict) {
				throw new RuntimeException("Missing field '$name'");
			}
			// Check if the value is an array or an stdClass
			if(is_array($value) || $value instanceof \stdClass) {
				// Get class type associated with the property
				$type = $property->getType();
				if($type !== null) {
					$class = new ReflectionClass($type->getName());
					if(self::hasTrait($class, MarshalTrait::class)) {
						$value = $class->getMethod("unmarshal")->invoke(
							null, $value, $strict
						);
					}
				}
			}
			$instance->{$property->getName()} = $value;
		}
		return $instance;
	}

	private static function getField(ReflectionProperty $property): ?Field {
		$attribute = $property->getAttributes(Field::class)[0] ?? null;
		if($attribute === null) {
			return null;
		}
		$field = $attribute->newInstance();
		if(!($field instanceof Field)) {
			throw new RuntimeException("Field attribute must be an instance of Field");
		}
		return $field;
	}

	/**
	 * Returns true if a given class has a trait on it
	 *
	 * @param ReflectionClass $class
	 * @param string $traitClass
	 * @return bool
	 */
	private static function hasTrait(ReflectionClass $class, string $traitClass): bool {
		return count(array_filter($class->getTraits(), fn(ReflectionClass $trait) => $trait->getName() === $traitClass)) === 1;
	}

	private static function checkType(ReflectionProperty $property, mixed $value): void {
		$type = $property->getType();
		if($type === null) {
			return;
		}

	}

}