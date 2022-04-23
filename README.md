# libMarshal
A small marshalling library used to encode/decode data from classes

## How to use?
Using the library with objects is extremely simple.

### MarshalTrait
This trait is the logic behind the marshaling/unmarshaling of objects. Like any other trait, you can use it through `use MarshalTrait`.
Furthermore, this trait gives you access to two methods:
- A class method: `marshal()` - Marshals the data into an array
- A static method: `unmarshal(array $data, bool $strict)` - Unmarshals the data back into an object

### Field
This attribute is one of the largest parts of the library as it is what determines what `MarshalTrait` marshals/unmarshals.
If a property is not annotated with this attribute, it will not be handled.

The Field attribute accepts one argument: `name`. If no name is specified, `MarshalTrait` will use the property name as the name to marshal/unmarshal with.

As examples:
```php
#[Field]
public string $name; // When encoded, it will output to ["name" => x]
#[Field(name: "Specific Name")]
public string $specificName; // When encoded, it will output to ["Specific Name" => x]
public string $hiddenName; // This field will not be included in either marshalling methods.
```

### Using Objects in properties
There are two main ways that you can use objects in properties.

The first (and easiest) is to use the `MarshalTrait` trait on the object's class. Like explained above, both `MarshalTrait` trait and the `Field` attribute on the properties are required.
Here is an example on how you would use that:
```php
class Range {
	use MarshalTrait;
	
	#[Field]
	public int $min = 0;
	#[Field]
	public int $max = 10;
	#[Field]
	public int $default = 0;
}

class Options {
	use MarshalTrait;
	
	#[Field]
	public string $username = "TestUserName";
	#[Field]
	public Range $range;
}
```
The second way can be found below in the `Parser` section.

### Parser
In this library, parsing is done by using the `Parseable` interface. This interface is simple and only consists of two methods:
- `parse(mixed $data)` - This takes in the raw data and returns it as a parsed form (whatever that may be).
- `serialize(mixed $data)` - This takes in the parsed data and returns it back into a raw form.

By default, the `Parseable` interface accepts the `mixed` type (though it allows for using PHPStan templates to specify the type). This can be restricted further by using any of the sub-interfaces of `Parseable`.
These sub-interfaces are all primitives and each modify the parameters through annotation while also changing the return type where applicable.
The full list of parseable types are:
- `ArrayParseable` - This is a parseable type that accepts an array.
- `BooleanParseable` - This is a parseable type that accepts a boolean.
- `FloatParseable` - This is a parseable type that accepts a float.
- `IntParseable` - This is a parseable type that accepts an integer.
- `ObjectParseable` - This is a parseable type that accepts an object.
- `StringParseable` - This is a parseable type that accepts a string.

After selecting the proper interface that fits your use case, you can implement the interface and use your own logic to parse/serialize the data. An example of this is shown below:
```php
class MyParser implements Parseable {
    public function parse(mixed $data) : mixed {
        return $data;
    }
    
    public function serialize(mixed $data) : mixed {
        return $data;
    }
}
```

From there, all you need to do is pass the class string to the `Field` attribute like so:
```php
#[Field(parser: MyParser::class)]
public string $name;
```
If used correctly, the parser is a very powerful tool that allows for a lot of flexibility in how you can use the library.


### Saving / Loading
By default, the `MarshalTrait` trait comes with support for two file formats:
- `json` - This comes with the static method `loadFromJson(string $fileName)` and the class method `saveToJson(string $fileName)`.
- `yaml` - This comes with the static method `loadFromYaml(string $fileName)` and the class method `saveToYaml(string $fileName)`.

Each contain arguments that wrap their respective encoding and decoding methods.

### Example
Here is a full example:
```php
class User {
	use MarshalTrait;
	
	public function __construct(
        #[Field(name: "first-name")]
        public string $firstName,
        #[Field(name: "last-name")]
        public string $lastName,
        #[Field]
        public int $age,
        #[Field]
        public string $email
	) {}
}

// NOTE: This uses promoted properties to make it easier to construct.
// You can learn more about this below.

// Marshalling
$user = new User(firstName: "John", lastName: "Doe", age: 30, email: "johndoe@gmail.com");
$data = $user->marshal(); // ["first-name" => "John", "last-name" => "Doe", "age" => 30, "email" => "johndoe@gmail.com"]

$data["first-name"] = "Jane"; // Changing the first name
$data["email"] = "janedoe@gmail.com"; // Changing the email

// Unmarshalling
$user = User::unmarshal($data); // User(firstName: "Jane", lastName: "Doe", age: 30, email: "janedoe@gmail.com")
```

### Limitations
As the library stands, the constructor is never called when creating an object. This is intentional as argument ordering may have unintended side effects.
If you want to be able to pass in parameters to an object, you can take advantage of PHP's promoted properties like so:
```php
class User {
	use MarshalTrait;

	public function __construct(
		#[Field(name: "first-name")]
		public string $firstName,
		#[Field(name: "last-name")]
		public string $lastName,
		#[Field]
		public int $age,
		#[Field]
		public string $email
	) {}
}
```
This will allow you to create the objects through a constructor as usual while also declaring the properties.


## Roadmap
At the moment, there are a few improvements that can be/or are being worked on. Here is a list of some of those improvements:
- [ ] Add a limit to recursive objects when marshalling/unmarshalling (50?)
- [ ] Cache properties for performance benefits


## Issues
Any issues/suggestion can be reported [here](https://github.com/sylvrs/libMarshal/issues).
