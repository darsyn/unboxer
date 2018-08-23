Simple utility to unbox complex data structures (objects) to native data types.

## Brief Example

```php
<?php

use Darsyn\Unboxer\Unboxer;
use Darsyn\Unboxer\UnboxableInterface;
use Darsyn\Unboxer\UnboxingException;

try {
    $data = [
        'one' => [
            'two' => 'a string',
            'three' => 72634,
        ],
        'four' => [
            'five' => null,
            'six' => 123.45,
        ],
        'seven' => [
            'eight' => [
                new class extends \RuntimeException implements UnboxableInterface {
                    public function __unbox()
                    {
                        return new \RuntimeException;
                    }
                },
            ],
        ],
    ];
} catch (UnboxingException $e) {
    echo $e->getMessage();
    // Could not unbox to native data types, encountered type "\RuntimeException" at path "seven.eight.0.{class}".
}
```

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)

If you make a contribution (submit a pull request), don't forget to add your
name here!
