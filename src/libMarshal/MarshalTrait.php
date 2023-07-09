<?php

declare(strict_types=1);

namespace libMarshal;

use libMarshal\attributes\Exclude;
use libMarshal\attributes\Field;
use libMarshal\exception\FileNotFoundException;
use libMarshal\exception\UnmarshalException;
use libMarshal\parser\Parseable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use function array_filter;
use function array_map;
use function count;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function get_debug_type;
use function implode;
use function in_array;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function method_exists;
use function yaml_emit_file;
use function yaml_parse_file;

/**
 * This is the main trait used for marshaling/demarshaling data.
 *
 * A good use case for this would be with data classes. For example:
 *
 * ```php
 * class MyDataClass {
 * 	  use MarshalTrait;
 *
 *     public int $testValue;
 *     public bool $testBool;
 *     public string $testString;
 * }
 * ```
 *
 * By default, all fields are marshaled. If you want to exclude a field,
 * you can use the {@link Exclude} attribute like so:
 *
 * ```php
 * final class MyDataClass {
 * 	  use MarshalTrait;
 *
 *   public int $testValue;
 *   public bool $testBool;
 *   public string $testString;
 *   #[Exclude]
 *   public string $excludedField;
 * }
 * ```
 */
trait MarshalTrait {
	/**
	 * A ReflectionClass instance used to fetch properties, values, etc.
	 * @var ReflectionClass<static>
	 */
	private static ReflectionClass $reflectedInstance;
	/**
	 * A list of {@link PropertyHolder} instances used to fetch properties, field attributes, etc.
	 * @var array<int, PropertyHolder<mixed, mixed>>
	 */
	private static array $cachedHolders;

	/**
	 * @return array<string, mixed>
	 */
	public function marshal(): array {
		$data = [];
		foreach (self::getHolders() as $holder) {
			// Get the reflection property + field attribute from the holder
			[$property, $field] = $holder->asArray();
			// Get the current value of the property
			$value = $this->{$property->getName()};
			$parser = $holder->getParser();
			// Update the array with the value
			$name = $field->name !== "" ? $field->name : $property->getName();
			$data[$name] = match (true) {
				// If the value is an object and has any type classes with the trait, we override
				is_object($value) && method_exists($value, "marshal") && count($holder->getTypeClasses()) > 0 => $value->marshal(),
				// If the holder has an associated parser, use that for marshaling
				$parser instanceof Parseable => $parser->serialize($value),
				// Otherwise, use the default value
				default => $value
			};
		}
		return $data;
	}

	/**
	 * @param array<string, mixed> $data - The data to unmarshal with
	 * @param bool $strict - Whether to throw an exception if a field is missing
	 *
	 * @throws UnmarshalException
	 */
	public static function unmarshal(array $data, bool $strict = true): static {
		// Create a new instance of the class
		/** @var static $instance */
		$instance = self::getReflectedInstance()->newInstanceWithoutConstructor();
		foreach (self::getHolders() as $holder) {
			// Get the property and field from the holder
			[$property, $field] = $holder->asArray();

			// Get the name of the property
			$name = $field->name !== "" ? $field->name : $property->getName();

			// Do not set the field if the user allows it to be uninitialized
			if (!isset($data[$name]) && !$property->hasDefaultValue() && $field->allowUninitialized) {
				continue;
			}

			// Fetch the value
			$value = $data[$name] ?? $property->getDefaultValue();
			// If the value is null, doesn't allow null, & strict is true, throw an exception
			if ($value === null && !$holder->allowsNull() && $strict) {
				throw new UnmarshalException("Missing field '$name'");
			}
			if (($parser = $holder->getParser()) instanceof Parseable) {
				// If the holder has an associated parser, use that for unmarshaling
				$value = $parser->parse($value);
			} else if (is_array($value) && count($holder->getTypeClasses()) > 0) {
				// If the value is an array, we can check if it has the MarshalTrait and if so, we can unmarshal it
				foreach ($holder->getTypeClasses() as $type) {
					try {
						$method = $type->getMethod(__FUNCTION__);
						$value = $method->invoke($instance, $value, $strict);
						break;
					} catch (ReflectionException|UnmarshalException) {
						// We don't care about this exception, we just want to try the next type
					}
				}
			} else {
				// Ensure that the value is of the correct type
				self::checkType($property, $value);
			}
			// Set the value on the instance
			$instance->{$property->getName()} = $value;
		}
		return $instance;
	}

	/**
	 * This method is used to marshal the object and save it into a YAML file
	 *
	 * @param array<class-string, callable> $callbacks
	 */
	public function saveToYaml(string $fileName, int $encoding = YAML_ANY_ENCODING, int $linebreak = YAML_ANY_BREAK, array $callbacks = []): bool {
		return yaml_emit_file(
			filename: $fileName,
			data: $this->marshal(),
			encoding: $encoding,
			linebreak: $linebreak,
			/** @phpstan-ignore-next-line - stubs for this are wrong & don't include the callbacks param */
			callbacks: $callbacks
		);
	}

	/**
	 * This method is used to load YAML data from a file and unmarshal it into an instance of the trait user
	 *
	 * @param array<class-string, callable> $callbacks
	 * @throws UnmarshalException|FileNotFoundException
	 */
	public static function loadFromYaml(string $fileName, bool $strict = true, int $pos = 0, ?int &$ndocs = null, array $callbacks = []): static {
		if (!file_exists($fileName)) {
			throw new FileNotFoundException("The file '$fileName' does not exist");
		}

		// parse and verify the data
		/** @phpstan-ignore-next-line - stubs for this are wrong & don't include the callbacks param */
		$data = yaml_parse_file(filename: $fileName, pos: $pos, ndocs: $ndocs, callbacks: $callbacks);
		if (!is_array($data)) {
			throw new UnmarshalException("Data loaded from file '$fileName' is not a valid object");
		}
		return self::unmarshal(data: $data, strict: $strict);
	}

	/**
	 * Encodes the object into a JSON string or throws an error on failure
	 *
	 * @param int<1, max> $depth
	 * @throws JsonException
	 */
	public function encodeToJson(int $flags = 0, int $depth = 512): string {
		return json_encode(value: $this->marshal(), flags: $flags | JSON_THROW_ON_ERROR, depth: $depth);
	}

	/**
	 * Marshals the object and saves it into a JSON file
	 *
	 * @param int<1, max> $depth
	 * @return int - The number of bytes written to the file or false on failure
	 * @throws JsonException
	 */
	public function saveToJson(string $fileName, int $flags = 0, int $depth = 512): int {
		$data = file_put_contents(filename: $fileName, data: $this->encodeToJson(flags: $flags, depth: $depth));
		if (!is_int($data)) {
			throw new FileSaveException("Failed to write data to file '$fileName'");
		}
		return $data;
	}

	/**
	 * Loads JSON data from a file and unmarshal it into an instance of the trait user
	 *
	 * @param int<1, max> $depth
	 * @throws UnmarshalException
	 */
	public static function loadFromJson(string $fileName, bool $strict = true, int $depth = 512, int $flags = 0): static {
		if (!file_exists($fileName)) {
			throw new FileNotFoundException("The file '$fileName' does not exist");
		}

		$raw = file_get_contents(filename: $fileName);
		if ($raw === false) {
			throw new UnmarshalException("Failed to read file '$fileName'");
		}
		return self::decodeFromJson(raw: $raw, strict: $strict, depth: $depth, flags: $flags);
	}

	/**
	 * Decodes the object from a JSON string and returns it as an instance of the trait user
	 * @param int<1, max> $depth
	 */
	public static function decodeFromJson(string $raw, bool $strict, int $depth = 512, int $flags = 0): static {
		$data = json_decode(json: $raw, associative: true, depth: $depth, flags: $flags);
		if (!is_array($data)) {
			throw new UnmarshalException("Data loaded from JSON is not a valid object");
		}
		return self::unmarshal(data: $data, strict: $strict);
	}

	/**
	 * @return ReflectionClass<static>
	 */
	private static function getReflectedInstance(): ReflectionClass {
		return self::$reflectedInstance ??= new ReflectionClass(static::class);
	}

	/**
	 * A lazy getter/initializer for getting the {@link PropertyHolder} instances for the implementing class.
	 *
	 * @return array<int, PropertyHolder<mixed, mixed>>
	 */
	private static function getHolders(): array {
		return self::$cachedHolders ??= array_map(
			callback: static fn(ReflectionProperty $property) => new PropertyHolder(
				property: $property,
				field: self::getField($property)
			),
			array: array_filter(
				array: self::getReflectedInstance()->getProperties(),
				// exclude all properties with the Exclude attribute
				callback: fn(ReflectionProperty $property): bool => !self::hasAttribute($property, Exclude::class) && !$property->isStatic()
			)
		);
	}

	/**
	 * Attempts to get the Field attribute from a property. If it doesn't exist, a new instance is created.
	 *
	 * @return Field<mixed, mixed>
	 */
	private static function getField(ReflectionProperty $property): Field {
		/** @var array<ReflectionAttribute<Field<mixed, mixed>>> $attributes */
		$attributes = array_filter(
			$property->getAttributes(),
			fn (ReflectionAttribute $attribute): bool => is_a($attribute->getName(), Field::class, true)
		);

		if (count($attributes) > 1) {
			throw new UnmarshalException("Property '{$property->getName()}' has more than one Field attribute");
		}
		return count($attributes) === 1 ? $attributes[0]->newInstance() : new Field();
	}

	/**
	 * Returns true if the property has the specified attribute
	 *
	 * @param class-string<object> $attributeName
	 */
	private static function hasAttribute(ReflectionProperty $property, string $attributeName): bool {
		return count($property->getAttributes($attributeName)) > 0;
	}

	/**
	 * This method is used as a way to check if a property's types are compatible with the value being set
	 *
	 * @throws UnmarshalException
	 */
	private static function checkType(ReflectionProperty $property, mixed $value): void {
		$type = $property->getType();
		if ($type === null || ($value === null && $type->allowsNull())) {
			return;
		}
		$valueTypeName = get_debug_type($value);
		if ($type instanceof ReflectionNamedType && $valueTypeName !== $type->getName() && !self::hasEdgeCase($type, $value)) {
			throw new UnmarshalException("Field '{$property->getName()}' must be of type '{$type->getName()}', got '$valueTypeName'");
		} else if ($type instanceof ReflectionUnionType && !in_array($valueTypeName, ($types = self::getTypeNames($type)), true) && !self::hasEdgeCase($type, $value)) {
			$imploded = implode(separator: ", ", array: $types);
			throw new UnmarshalException("Field '{$property->getName()}' must be one of the types ($imploded), got '$valueTypeName'");
		}
	}

	/**
	 * This method is used to compile a list of names associated with a given type
	 * @return array<string>
	 */
	private static function getTypeNames(ReflectionType $type): array {
		return array_map(
			callback: fn(ReflectionNamedType $type) => $type->getName(),
			array: match (true) {
				$type instanceof ReflectionNamedType => [$type],
				$type instanceof ReflectionUnionType => $type->getTypes(),
				default => []
			}
		);
	}

	/**
	 * This method is used as a way to check for weird edge cases that can occur between types.
	 */
	private static function hasEdgeCase(ReflectionType $type, mixed $value): bool {
		$types = self::getTypeNames($type);
		return match (true) {
			// If the value is an int, it can be implicitly cast to a float
			get_debug_type($value) === "int" && in_array(needle: "float", haystack: $types, strict: true) => true,
			default => false
		};
	}

}