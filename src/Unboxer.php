<?php declare(strict_types=1);

namespace Darsyn\Unboxer;

class Unboxer
{
    public static function unbox($data)
    {
        return static::convertToNativeDataType($data);
    }

    protected static function convertToNativeDataType($data, array $keys = [])
    {
        if ($data === null || \is_scalar($data)) {
            return $data;
        } elseif (\is_object($data)) {
            $class = strpos(get_class($data), '@') ? '{class}' : get_class($data);
            $class = trim(strrchr($class, '\\') ?: $class, '\\');
            if ($data instanceof UnboxableInterface) {
                array_push($keys, $class);
                return static::convertToNativeDataType($data->__unbox(), $keys);
            } elseif (\get_class($data) === \stdClass::class) {
                array_push($keys, $class);
                return static::convertToNativeDataType((array) $data, $keys);
            }
        } elseif (\is_array($data)) {
            array_walk($data, function (&$value, $key) use ($keys) {
                array_push($keys, $key);
                $value = static::convertToNativeDataType($value, $keys);
            });
            return $data;
        }
        throw new UnboxingException(sprintf(
            'Could not unbox to native data types, encountered type "%s" at path "%s".',
            is_object($data) ? '\\' . get_class($data) : gettype($data),
            implode('.', $keys)
        ));
    }
}
