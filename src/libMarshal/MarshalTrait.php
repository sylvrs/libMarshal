<?php
declare(strict_types=1);

namespace libMarshal;


use libMarshal\attributes\Field;
use libMarshal\exception\FileNotFoundException;
use libMarshal\exception\GeneralMarshalException;
use libMarshal\exception\UnmarshalException;
use libMarshal\parser\Parseable;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionType;
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
				if(($parser = $holder->getParser()) instanceof Parseable) {
					// If the holder has an associated parser, use that for marshaling
					$value = $parser->serialize($value);
				} else if(is_object($value) && count($holder->getTypeClasses()) > 0) {
					// If the value is an object and has any type classes with the trait, we override
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
			// Create a new instance of the class
			/** @var static $instance */
			$instance = self::getReflectedInstance()->newInstanceWithoutConstructor();
			foreach(self::getHolders() as $holder) {
				// Get the property and field from the holder
				[$property, $field] = $holder->asArray();

				// Get the name of the property
				$name = $field->name !== "" ? $field->name : $property->getName();
				// Fetch the value
				$value = $data[$name] ?? null;
				// If the value is null, doesn't allow null, & strict is true, throw an exception
				if($value === null && !$holder->allowsNull() && $strict) {
					throw new UnmarshalException("Missing field '$name'");
				}
				if(($parser = $holder->getParser()) instanceof Parseable) {
					// If the holder has an associated parser, use that for unmarshaling
					$value = $parser->parse($value);
				} else if(is_array($value) && count($holder->getTypeClasses()) > 0) {
					// If the value is an array, we can check if it has the MarshalTrait and if so, we can unmarshal it
					foreach($holder->getTypeClasses() as $type) {
						try {
							$method = $type->getMethod(__FUNCTION__);
							$value = $method->invoke($instance, $value, $strict);
							break;
						}
						catch(ReflectionException|UnmarshalException) {
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
		} catch(ReflectionException $exception) {
			throw new GeneralMarshalException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * This method is used to marshal the object and save it into a YAML file
	 *
	 * @param string $fileName
	 * @param int $encoding
	 * @param int $linebreak
	 * @param array $callbacks
	 * @return bool
	 * @throws GeneralMarshalException
	 */
	public function saveToYaml(string $fileName, int $encoding = YAML_ANY_ENCODING, int $linebreak = YAML_ANY_BREAK, array $callbacks = []): bool {
		return yaml_emit_file(
			filename: $fileName,
			data: $this->marshal(),
			encoding: $encoding,
			linebreak: $linebreak,
			callbacks: $callbacks
		);
	}

	/**
	 * This method is used to load YAML data from a file and unmarshal it into an instance of the trait user
	 *
	 * @param string $fileName
	 * @param bool $strict
	 * @param int $pos
	 * @param int|null $ndocs
	 * @param array $callbacks
	 * @return static
	 * @throws GeneralMarshalException
	 * @throws UnmarshalException
	 */
	public static function loadFromYaml(string $fileName, bool $strict = true, int $pos = 0, ?int &$ndocs = null, array $callbacks = []): static {
		if(!file_exists($fileName)) {
			throw new FileNotFoundException("The file '$fileName' does not exist");
		}
		return self::unmarshal(
			data: yaml_parse_file(
				filename: $fileName,
				pos: $pos,
				ndocs: $ndocs,
				callbacks: $callbacks
			),
			strict: $strict
		);
	}

	/**
	 * This method is used to marshal the object and save it into a JSON file
	 *
	 * @param string $fileName
	 * @param int $flags
	 * @param int $depth
	 * @return int|false
	 * @throws GeneralMarshalException
	 */
	public function saveToJson(string $fileName, int $flags = 0, int $depth = 512): int|false {
		return file_put_contents(
			filename: $fileName,
			data: json_encode(
				value: $this->marshal(),
				flags: $flags,
				depth: $depth
			)
		);
	}

	/**
	 * This method is used to load JSON data from a file and unmarshal it into an instance of the trait user
	 *
	 * @param string $fileName
	 * @param bool $strict
	 * @param int $depth
	 * @param int $flags
	 * @return static
	 * @throws GeneralMarshalException
	 * @throws UnmarshalException
	 */
	public static function loadFromJson(string $fileName, bool $strict = true, int $depth = 512, int $flags = 0): static {
		if(!file_exists($fileName)) {
			throw new FileNotFoundException("The file '$fileName' does not exist");
		}
		return self::unmarshal(
			data: json_decode(
				json: file_get_contents(
					filename: $fileName
				),
				associative: true,
				depth: $depth,
				flags: $flags
			),
			strict: $strict
		);
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
	 * This method is used as a way to check if a property's types are compatible with the value being set
	 *
	 * @throws GeneralMarshalException
	 */
	private static function checkType(ReflectionProperty $property, mixed $value): void {
		$type = $property->getType();
		if($type === null || ($value === null && $type->allowsNull())) {
			return;
		}

		$valueTypeName = get_debug_type($value);
		if($type instanceof ReflectionNamedType && $valueTypeName !== $type->getName() && !self::hasEdgeCase($type, $value)) {
			throw new GeneralMarshalException("Field '{$property->getName()}' must be of type '{$type->getName()}', got '$valueTypeName'");
		} else if($type instanceof ReflectionUnionType && !in_array($valueTypeName, $type->getTypes(), true) && !self::hasEdgeCase($type, $value)) {
			$types = implode(
				separator: ",",
				array: array_map(
					callback: fn(ReflectionNamedType $type) => $type->getName(),
					array: $type->getTypes()
				)
			);
			throw new GeneralMarshalException("Field '{$property->getName()}' must be one of the types ($types), got '$valueTypeName'");
		}
	}

	/**
	 * This method is used as a way to check for weird edge cases that can occur between types.
	 *
	 * @param ReflectionType $type
	 * @param mixed $value
	 * @return bool
	 */
	private static function hasEdgeCase(ReflectionType $type, mixed $value): bool {
		// Map the types to a list of their names
		$types = array_map(
			callback: fn(ReflectionNamedType $type) => $type->getName(),
			array: match(true) {
				$type instanceof ReflectionNamedType => [$type],
				$type instanceof ReflectionUnionType => $type->getTypes(),
				default => []
			}
		);

		return match(true) {
			// If the value is an int, it can be implicitly cast to a float
			get_debug_type($value) === "int" && in_array(needle: "float", haystack: $types, strict: true) => true,
			default => false
		};
	}

}