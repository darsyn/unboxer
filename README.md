Simple utility to unbox complex data structures (objects) to native data types,
suitable for encoding (for example, JSON).

# Documentation

## Code of Conduct

This project includes and adheres to the [Contributor Covenant as a Code of
Conduct](CODE_OF_CONDUCT.md).

## Brief Example

```php
<?php declare(strict_types=1);

use Darsyn\Unboxer\Unboxer;
use Darsyn\Unboxer\UnboxableInterface;
use Darsyn\Unboxer\UnboxingException;

class ConversionRate {
    private $rate;
    public function __construct(float $rate) { $this->rate = $rate; }
    public function getRate(): float { return $this->rate; }
}
class CurrencyConversion implements UnboxableInterface {
    private $amount;
    private $rate;
    public function __unbox() { return $this->amount * $this->rate->getRate(); }
    public function __construct(int $amount, ConversionRate $rate) {
        $this->amount = $amount;
        $this->rate = $rate;
    }
}

$data = [
    'scalars' => [
        'string' => 'a fancy string',
        'integer' => 72634,
        'float' => 123.45,
        'null' => null,
    ],
    'object' => (function (string $interval): object {
        $now = new \DateTimeImmutable;
        $dates = new \stdClass;
        $dates->now = $now;
        $dates->nowMinusInterval = $now->sub(new \DateInterval($interval));
        return $dates;
    })('PT1H'),
    'anonymous-class' => new class implements UnboxableInterface {
        public function __unbox() {
            return new \RuntimeException('This is a RuntimeException message.');
        }
    },
    'class' => new CurrencyConversion(750, new ConversionRate(1.3846)),
];

try {
    $output = (new Unboxer)->unbox($data);
} catch (UnboxingException $e) {
    echo $e->getMessage();
}
```

`var_dump`ing the variable `$output` results in:

```
array(4) {
    'scalars' =>
    array(4) {
        'string' =>
        string(14) "a fancy string"
        'integer' =>
        int(72634)
        'float' =>
        double(123.45)
        'null' =>
        NULL
    }
    'object' =>
    array(2) {
        'now' =>
        string(25) "2019-02-05T13:15:32+00:00"
        'nowMinusInterval' =>
        string(25) "2019-02-05T12:15:32+00:00"
    }
    'anonymous-class' =>
    string(35) "This is a RuntimeException message."
    'class' =>
    double(1038.45)
}
```

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)

If you make a contribution (submit a pull request), don't forget to add your
name here!
