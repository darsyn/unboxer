Simple utility to unbox complex data structures (objects) to native data types,
suitable for encoding (for example, JSON).

# Documentation

## Code of Conduct

This project includes and adheres to the [Contributor Covenant as a Code of
Conduct](CODE_OF_CONDUCT.md).

## Supported Types

This library returns all scalar and null values as-is, plus recursively
processing all array (and `stdClass`) types.

When this library encounters an object that is an instance of a known type, it
will attempt to convert it by using the return value of a specific method.
Object types supported by this library out-of-the-box include:

- Dates (objects implementing `DateTimeInterface`) which are converted to
  strings according to RFC3339 (eg, `2019-02-05T12:15:32+00:00`).
- Timezones (objects implementing `DateTimeZone`) which result in a string
  containing the timezone name (eg, `America/Vancouver`).
- Exceptions and errors (objects implementing `Throwable`) which result in a
  string containing the exception message.
- JSON (objects implementing `JsonSerializable`) which result in the library
  recursively iterating over the JSON data returned.
- Doctrine array collections (objects implementing `ArrayCollection`) which
  result in the library iterating over each of the items inside the collection.

Additionally, any user-land object can implement `UnboxableInterface`. Similar
to `JsonSerializable::jsonSerialize()` method, the `__unbox` method can return
anything as a representation of its internal state.
It is recommended to return unboxable objects as-is, as everything returned from
`UnboxableInterface::__unbox` is recursively iterated over anyway.

## Brief Example

```php
<?php declare(strict_types=1);

use Darsyn\Unboxer\Unboxer;
use Darsyn\Unboxer\UnboxableInterface;
use Darsyn\Unboxer\UnboxingException;

class Group implements UnboxableInterface
{
    private string $name;
    public function __construct(string $name) {
        $this->name = $name;
    }
    public function __unbox() {
        return $this->name;
    }
}

class Options implements \JsonSerializable {
    private array $options;
    public function __construct(bool $active, bool $verified, \DateTimeZone $timezone) {
        $this->options = ['active' => $active, 'verified' => $verified, 'tz' => $timezone];
    }
    public function jsonSerialize() {
        return $this->options;
    }
}

class Member implements UnboxableInterface
{
    private int $id;
    private string $username;
    private ArrayCollection $groups;
    private ?Options $options;
    public function __construct(int $id, string $username, array $groups = [], ?Options $options = null) {
        $this->id = $id;
        $this->username = $username;
        $this->groups = new ArrayCollection($groups);
        $this->options = $options;
    }
    public function __unbox()
    {
        return [
            // Scalars are used as-is.
            'id' => $this->id,
            'username' => $this->username,
            // Objects of known types are returned as-is, but recursively iterated over.
            'groups' => $this->groups,
            // JSON-serializable objects are never actually run through json_encode().
            'options' => $this->options ?: [],
        ];
    }
}

$member = new Member(123, 'dr-evil', [
    new Group('admin'),
    new Group('moderator'),
    new Group('sharks-with-lasers'),
], new Options(true, false, new \DateTimeZone('America/Vancouver')));

try {
    $output = (new Unboxer)->unbox($member);
    var_dump($output);
} catch (UnboxingException $e) {
    echo $e->getMessage();
}
```

`var_dump`ing the variable `$output` results in:

```
array(4) {
  'id' =>
  int(123)
  'username' =>
  string(7) "dr-evil"
  'groups' =>
  array(3) {
    [0] =>
    string(5) "admin"
    [1] =>
    string(9) "moderator"
    [2] =>
    string(18) "sharks-with-lasers"
  }
  'options' =>
  array(3) {
    'active' =>
    bool(true)
    'verified' =>
    bool(false)
    'tz' =>
    string(17) "America/Vancouver"
  }
}
```

Note that returning multiple nested unboxable objects will result in the output
collapsing down into a single value:

```php
<?php declare(strict_types=1);

use Darsyn\Unboxer\Unboxer;
use Darsyn\Unboxer\UnboxableInterface;
use Darsyn\Unboxer\UnboxingException;

$data = new class implements UnboxableInterface {
    public function __unbox() {
        return new class implements UnboxableInterface {
            public function __unbox() {
                return new class implements UnboxableInterface {
                    public function __unbox() {
                        return new \RuntimeException('Error Message');
                    }
                };
            }
        };
    }
};

try {
    $output = (new Unboxer)->unbox($data);
    var_dump($output);
} catch (UnboxingException $e) {
    echo $e->getMessage();
}
```

```
string(13) "Error Message"
```

## Extending

Additional known object types can be added by extending `Unboxer` and overriding
the `getKnownDataTypes` method. For each known object type, either a closure or
an array specifying which method to call on the object may can specified:

```php
<?php declare(strict_types=1);

use Darsyn\Unboxer\Unboxer;

class MyUnboxer extends Unboxer {
    protected function getKnownDataMethods(): iterable {
        // Don't forget to return the known data methods defined in the original, parent Unboxer.
        // The parent returns an array, but any iterable is acceptable.
        yield from parent::getKnownDataMethods();

        // Config array example.
        // Must be in the format ['methodToCall', ['optional', 'arguments', 'array']].
        yield \DateTimeInterface::class => ['format', [\DateTimeInterface::RFC3339]];

        // Closure example.
        yield \DateTimeInterface::class => function (\DateTimeInterface $date): string {
            return $date->format(\DateTimeInterface::RFC3339);
        };
    }
}
```

The unboxer will, by default, convert any objects with the `__toString()` magic
method to a string. To turn this functionality off, extend `Unboxer` and
override the class constant `STRINGIFY_OBJECTS`.

```php
<?php declare(strict_types=1);

use Darsyn\Unboxer\Unboxer;

class MyUnboxer extends Unboxer {
    public const STRINGIFY_OBJECTS = false;
}
```

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)

If you make a contribution (submit a pull request), don't forget to add your
name here!
