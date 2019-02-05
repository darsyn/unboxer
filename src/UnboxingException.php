<?php declare(strict_types=1);

namespace Darsyn\Unboxer;

class UnboxingException extends \RuntimeException
{
    private $data;
    private $keys = [];

    public function __construct(string $message, $data, array $keys, ?\Throwable $previous = null)
    {
        $this->data = $data;
        $this->keys = $keys;
        parent::__construct($message, 0, $previous);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getDataType(): string
    {
        return \is_object($this->data)
            ? (\strpos(\get_class($this->data), '@') ? '{class}' : \get_class($this->data))
            : \gettype($this->data);
    }

    public function getDataPath(): string
    {
        return \implode('.', $this->keys);
    }

    public function getKeys(): array
    {
        return $this->keys;
    }
}
