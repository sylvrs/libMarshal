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

The Field attribute accepts one argument: `name`. If no name is specified, `MarshalTrait` will use the property name as the name to encode with.

As examples:
```php
#[Field]
public string $name; // When encoded, it will output to ["name" => x]
#[Field(name: "Specific Name")]
public string $specificName; // When encoded, it will output to ["Specific Name" => x]
public string $hiddenName; // This field will not be included in either marshalling methods.
```

### Using Objects in properties
The only prerequisite behind marshalling/unmarshalling an embedded object is to have the class set up with the `MarshalTrait` and `Field`s. Here is an example on how you would use that:
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

### Example
Here is a full example:
```php
class User {
    use MarshalTrait;
    
    #[Field(name: "first-name")]
		public string $firstName;
		#[Field(name: "last-name")]
		public string $lastName;
		#[Field]
		public int $age;
		#[Field]
		public string $email;
}
```
From here, you can create and marshal the object easily through `User->marshal()`. This will return an array of results.
To return the data back into an object, all you have to do is `User::unmarshal($data)`


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
