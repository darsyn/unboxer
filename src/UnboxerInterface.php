<?php declare(strict_types=1);

namespace Darsyn\Unboxer;

interface UnboxerInterface
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function unbox($value);
}
