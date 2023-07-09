# libMarshal
A small marshalling library used to encode/decode data from classes.

## Installation
### Composer
To install this library through composer, run the following command:
```
composer require sylvrs/libmarshal
```
### Virion
The virion for this library can be accessed [here](https://poggit.pmmp.io/ci/sylvrs/libMarshal/libMarshal).

## Basic Example
Here is a basic example on how this library is used:
```php
class User {
	use MarshalTrait;
	
	public function __construct(
            #[Field(name: "first-name")]
            public string $firstName,
            #[Field(name: "last-name")]
            public string $lastName,
            public int $age,
            public string $email,
            #[Exclude]
            public string $internalData = "..."
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

## Wiki
To learn about how to use the library, please consult the wiki [here](https://github.com/sylvrs/libMarshal/wiki).

## Roadmap
At the moment, there are a few improvements that can be/or are being worked on. Here is a list of some of those improvements:
- [ ] Add a limit to recursive objects when marshalling/unmarshalling (50?)
- [X] Cache properties for performance benefits

## Issues
Any issues/suggestion can be reported [here](https://github.com/sylvrs/libMarshal/issues).
